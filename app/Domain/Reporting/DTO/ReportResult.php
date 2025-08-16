<?php

declare(strict_types=1);

namespace App\Domain\Reporting\DTO;

readonly class ReportResult
{
    public function __construct(
        public array $rows,
        public array $columns,
        public array $meta,
        public string $generated_at
    ) {}

    /**
     * Convert to array for serialization
     */
    public function toArray(): array
    {
        return [
            'rows' => $this->rows,
            'columns' => $this->columns,
            'meta' => $this->meta,
            'generated_at' => $this->generated_at,
        ];
    }
}