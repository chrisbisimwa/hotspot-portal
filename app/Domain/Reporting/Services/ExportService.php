<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Services;

use App\Domain\Reporting\DTO\ExportRequestData;
use App\Jobs\ProcessExportJob;
use App\Models\Export;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

class ExportService
{
    public function __construct(
        private ReportRegistry $registry
    ) {}

    /**
     * Request a new export
     */
    public function requestExport(
        string $reportKey,
        string $format,
        array $filters,
        User $user
    ): Export {
        // Validate the report exists
        $this->registry->get($reportKey);

        // Validate format is allowed
        $builder = $this->registry->get($reportKey);
        if (!in_array($format, $builder->allowedFormats())) {
            throw new \InvalidArgumentException("Format '{$format}' is not allowed for report '{$reportKey}'");
        }

        // Create export record
        $export = Export::create([
            'report_key' => $reportKey,
            'format' => $format,
            'status' => 'queued',
            'requested_by' => $user->id,
            'filters' => $filters,
        ]);

        // Dispatch processing job
        $requestData = new ExportRequestData($reportKey, $format, $filters, $user);
        Queue::dispatch(new ProcessExportJob($export->id, $requestData));

        return $export;
    }

    /**
     * Generate a signed download URL for an export
     */
    public function generateDownloadUrl(Export $export): string
    {
        if ($export->status !== 'completed') {
            throw new \InvalidArgumentException('Export is not completed');
        }

        if (!$export->file_path) {
            throw new \InvalidArgumentException('Export has no file');
        }

        return route('admin.exports.download', $export, [], true);
    }

    /**
     * Check if user can access export
     */
    public function canUserAccessExport(Export $export, User $user): bool
    {
        // Admin can access all exports
        if ($user->hasRole('admin')) {
            return true;
        }

        // User can only access their own exports
        return $export->requested_by === $user->id;
    }
}