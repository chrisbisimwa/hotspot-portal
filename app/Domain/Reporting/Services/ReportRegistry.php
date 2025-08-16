<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Services;

use App\Domain\Reporting\Contracts\ReportBuilderInterface;
use App\Domain\Reporting\Exceptions\ReportException;
use InvalidArgumentException;

class ReportRegistry
{
    /** @var array<string, ReportBuilderInterface> */
    private array $builders = [];

    /**
     * Register a report builder
     */
    public function register(ReportBuilderInterface $builder): void
    {
        $this->builders[$builder->identifier()] = $builder;
    }

    /**
     * Get a report builder by identifier
     */
    public function get(string $identifier): ReportBuilderInterface
    {
        if (!isset($this->builders[$identifier])) {
            throw ReportException::unknownReport($identifier);
        }

        return $this->builders[$identifier];
    }

    /**
     * Get all registered builders
     */
    public function all(): array
    {
        return $this->builders;
    }

    /**
     * Get metadata for all registered reports
     */
    public function metadata(): array
    {
        $metadata = [];

        foreach ($this->builders as $builder) {
            $metadata[$builder->identifier()] = [
                'identifier' => $builder->identifier(),
                'title' => $builder->title(),
                'description' => $builder->description(),
                'default_format' => $builder->defaultFormat(),
                'allowed_formats' => $builder->allowedFormats(),
                'filters_schema' => $builder::filtersSchema(),
            ];
        }

        return $metadata;
    }

    /**
     * Check if a report builder is registered
     */
    public function has(string $identifier): bool
    {
        return isset($this->builders[$identifier]);
    }
}