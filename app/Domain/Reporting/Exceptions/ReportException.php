<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Exceptions;

use Exception;

class ReportException extends Exception
{
    /**
     * Create a new report exception for max rows exceeded
     */
    public static function maxRowsExceeded(int $actualRows, int $maxRows): self
    {
        return new self("Report has {$actualRows} rows, which exceeds the maximum of {$maxRows} rows.");
    }

    /**
     * Create a new report exception for unknown report
     */
    public static function unknownReport(string $reportKey): self
    {
        return new self("Unknown report: {$reportKey}");
    }

    /**
     * Create a new report exception for invalid filters
     */
    public static function invalidFilters(string $message): self
    {
        return new self("Invalid filters: {$message}");
    }

    /**
     * Create a new report exception for build failure
     */
    public static function buildFailed(string $reportKey, string $reason): self
    {
        return new self("Failed to build report '{$reportKey}': {$reason}");
    }
}