<?php

declare(strict_types=1);

namespace App\Domain\Reporting\DTO;

use App\Models\User;

readonly class ExportRequestData
{
    public function __construct(
        public string $report_key,
        public string $format,
        public array $filters,
        public User $requested_by
    ) {}

    /**
     * Convert to array for serialization
     */
    public function toArray(): array
    {
        return [
            'report_key' => $this->report_key,
            'format' => $this->format,
            'filters' => $this->filters,
            'requested_by' => $this->requested_by->id,
        ];
    }
}