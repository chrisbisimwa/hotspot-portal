<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Reporting\DTO\ExportRequestData;
use App\Domain\Reporting\Events\ExportCompleted;
use App\Domain\Reporting\Services\ReportRegistry;
use App\Models\Export;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 3;

    public function __construct(
        public int $exportId,
        public ExportRequestData $requestData
    ) {}

    public function handle(ReportRegistry $registry): void
    {
        $startTime = microtime(true);
        
        Log::info('ProcessExportJob: Starting export process', [
            'export_id' => $this->exportId,
            'report_key' => $this->requestData->report_key,
            'format' => $this->requestData->format,
        ]);

        $export = Export::find($this->exportId);
        if (!$export) {
            Log::error('ProcessExportJob: Export not found', ['export_id' => $this->exportId]);
            return;
        }

        try {
            // Update status to processing
            $export->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            // Get the report builder
            $builder = $registry->get($this->requestData->report_key);
            
            // Build the report
            $result = $builder->build($this->requestData->filters);
            
            // Generate the file
            $filename = $this->generateFilename();
            $filePath = $this->generateFile($result, $filename);
            
            // Update export with completion details
            $export->update([
                'status' => 'completed',
                'total_rows' => count($result->rows),
                'file_path' => $filePath,
                'finished_at' => now(),
                'meta' => array_merge($result->meta, [
                    'export_generated_at' => now()->toISOString(),
                    'execution_time_seconds' => round(microtime(true) - $startTime, 3),
                ]),
            ]);

            $executionTime = microtime(true) - $startTime;
            Log::info('ProcessExportJob: Export completed successfully', [
                'export_id' => $this->exportId,
                'execution_time_seconds' => round($executionTime, 3),
                'total_rows' => count($result->rows),
                'file_path' => $filePath,
            ]);

            // Dispatch completion event
            event(new ExportCompleted($export));

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            // Update export with error details
            $export->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
                'meta' => [
                    'error_occurred_at' => now()->toISOString(),
                    'execution_time_seconds' => round($executionTime, 3),
                ],
            ]);

            Log::error('ProcessExportJob: Export failed', [
                'export_id' => $this->exportId,
                'error' => $e->getMessage(),
                'execution_time_seconds' => round($executionTime, 3),
            ]);
            
            throw $e;
        }
    }

    private function generateFilename(): string
    {
        $uuid = Str::uuid()->toString();
        return "{$uuid}.{$this->requestData->format}";
    }

    private function generateFile($result, string $filename): string
    {
        $disk = config('exports.storage_disk', 'local');
        $path = "exports/{$filename}";

        if ($this->requestData->format === 'csv') {
            $csvContent = $this->generateCsv($result);
            Storage::disk($disk)->put($path, $csvContent);
        } elseif ($this->requestData->format === 'pdf') {
            $pdfContent = $this->generatePdf($result);
            Storage::disk($disk)->put($path, $pdfContent);
        } else {
            throw new \InvalidArgumentException("Unsupported format: {$this->requestData->format}");
        }

        return $path;
    }

    private function generateCsv($result): string
    {
        $output = fopen('php://temp', 'w');
        
        // Write headers
        $headers = array_column($result->columns, 'label');
        fputcsv($output, $headers);
        
        // Write data rows
        foreach ($result->rows as $row) {
            $csvRow = [];
            foreach ($result->columns as $column) {
                $csvRow[] = $row[$column['key']] ?? '';
            }
            fputcsv($output, $csvRow);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    private function generatePdf($result): string
    {
        $pdf = Pdf::loadView('reports.pdf.generic', [
            'result' => $result,
            'title' => $this->getReportTitle(),
        ]);
        
        return $pdf->output();
    }

    private function getReportTitle(): string
    {
        $titles = [
            'orders_summary' => 'Orders Summary Report',
            'payments_status_breakdown' => 'Payments Status Breakdown',
            'hotspot_usage' => 'Hotspot Usage Report',
            'user_growth' => 'User Growth Report',
        ];

        return $titles[$this->requestData->report_key] ?? 'Report';
    }

    public function tags(): array
    {
        return ['export', 'reporting', $this->requestData->report_key];
    }
}