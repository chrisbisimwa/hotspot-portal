<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Contracts;

use App\Domain\Reporting\DTO\ReportResult;

interface ReportBuilderInterface
{
    /**
     * Get unique identifier for this report
     */
    public function identifier(): string;

    /**
     * Get human-readable title for this report
     */
    public function title(): string;

    /**
     * Get description of what this report contains
     */
    public function description(): string;

    /**
     * Get default format for export
     */
    public function defaultFormat(): string;

    /**
     * Get allowed export formats
     */
    public function allowedFormats(): array;

    /**
     * Get filters schema for UI generation
     */
    public static function filtersSchema(): array;

    /**
     * Build the report with given filters
     */
    public function build(array $filters = []): ReportResult;
}