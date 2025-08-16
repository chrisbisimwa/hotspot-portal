<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Services;

use App\Domain\Reporting\Contracts\ReportBuilderInterface;
use App\Domain\Reporting\DTO\ReportResult;
use App\Domain\Reporting\Exceptions\ReportException;
use Carbon\Carbon;

abstract class AbstractReportBuilder implements ReportBuilderInterface
{
    /**
     * Get default format (CSV)
     */
    public function defaultFormat(): string
    {
        return 'csv';
    }

    /**
     * Get allowed export formats
     */
    public function allowedFormats(): array
    {
        return ['csv', 'pdf'];
    }

    /**
     * Validate and prepare filters
     */
    protected function prepareFilters(array $filters): array
    {
        $defaultFilters = $this->getDefaultFilters();
        $filters = array_merge($defaultFilters, $filters);

        // Validate required filters exist
        foreach ($this->getRequiredFilters() as $required) {
            if (!isset($filters[$required])) {
                throw ReportException::invalidFilters("Missing required filter: {$required}");
            }
        }

        return $this->normalizeFilters($filters);
    }

    /**
     * Get default date range filters (last 7 days)
     */
    protected function getDefaultDateRange(): array
    {
        return [
            'date_from' => Carbon::now()->subDays(7)->format('Y-m-d'),
            'date_to' => Carbon::now()->format('Y-m-d'),
        ];
    }

    /**
     * Normalize date filters
     */
    protected function normalizeFilters(array $filters): array
    {
        // Convert date strings to Carbon instances if present
        if (isset($filters['date_from'])) {
            $filters['date_from'] = Carbon::parse($filters['date_from'])->startOfDay();
        }
        
        if (isset($filters['date_to'])) {
            $filters['date_to'] = Carbon::parse($filters['date_to'])->endOfDay();
        }

        return $filters;
    }

    /**
     * Build result with row limit validation
     */
    protected function buildResult(array $rows, array $columns, array $meta = []): ReportResult
    {
        $maxRows = config('exports.max_rows', 50000);
        
        if (count($rows) > $maxRows) {
            $meta['truncated'] = true;
            $meta['total_rows'] = count($rows);
            $rows = array_slice($rows, 0, $maxRows);
            
            throw ReportException::maxRowsExceeded(count($rows), $maxRows);
        }

        $meta['total_rows'] = count($rows);
        $meta['cache_hit'] = false;

        return new ReportResult(
            $rows,
            $columns,
            $meta,
            Carbon::now()->toISOString()
        );
    }

    /**
     * Get default filters for this report
     */
    protected function getDefaultFilters(): array
    {
        return [];
    }

    /**
     * Get required filters for this report
     */
    protected function getRequiredFilters(): array
    {
        return [];
    }
}