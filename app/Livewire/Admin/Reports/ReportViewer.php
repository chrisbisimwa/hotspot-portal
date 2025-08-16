<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Reports;

use App\Domain\Reporting\Services\ExportService;
use App\Domain\Reporting\Services\ReportCacheService;
use App\Domain\Reporting\Services\ReportRegistry;
use Livewire\Component;

class ReportViewer extends Component
{
    public string $reportKey = '';
    public array $filters = [];
    public ?array $result = null;
    public bool $loading = false;
    public ?string $error = null;

    private ReportRegistry $registry;
    private ReportCacheService $cacheService;
    private ExportService $exportService;

    public function boot(
        ReportRegistry $registry,
        ReportCacheService $cacheService,
        ExportService $exportService
    ): void {
        $this->registry = $registry;
        $this->cacheService = $cacheService;
        $this->exportService = $exportService;
    }

    public function mount(string $reportKey): void
    {
        // Check authorization
        abort_unless(auth()->user()->hasRole('admin'), 403, 'Unauthorized access to reports');
        
        $this->reportKey = $reportKey;
        
        // Validate report exists
        if (!$this->registry->has($reportKey)) {
            abort(404, 'Report not found');
        }

        // Initialize filters with defaults
        $builder = $this->registry->get($reportKey);
        $filtersSchema = $builder::filtersSchema();
        
        foreach ($filtersSchema as $key => $type) {
            if ($type === 'date') {
                $this->filters[$key] = match($key) {
                    'date_from' => now()->subDays(7)->format('Y-m-d'),
                    'date_to' => now()->format('Y-m-d'),
                    default => now()->format('Y-m-d'),
                };
            }
        }

        // Load initial data
        $this->loadReport();
    }

    public function loadReport(): void
    {
        $this->loading = true;
        $this->error = null;

        try {
            $builder = $this->registry->get($this->reportKey);
            $result = $this->cacheService->getOrBuild($builder, $this->filters);
            
            $this->result = $result->toArray();
            $this->result['meta']['cache_hit'] = true; // Will be false if not cached
        } catch (\Exception $e) {
            $this->error = 'Failed to load report: ' . $e->getMessage();
            $this->result = null;
        } finally {
            $this->loading = false;
        }
    }

    public function exportReport(string $format): void
    {
        try {
            $export = $this->exportService->requestExport(
                $this->reportKey,
                $format,
                $this->filters,
                auth()->user()
            );

            session()->flash('message', 'Export has been queued. You will be notified when it\'s ready.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to request export: ' . $e->getMessage());
        }
    }

    public function getReportMetadataProperty(): array
    {
        $metadata = $this->registry->metadata();
        return $metadata[$this->reportKey] ?? [];
    }

    public function render()
    {
        return view('livewire.admin.reports.report-viewer')
            ->title($this->reportMetadata['title'] ?? 'Report Viewer');
    }
}