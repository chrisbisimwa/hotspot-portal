<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Builders;

use App\Domain\Reporting\DTO\ReportResult;
use App\Domain\Reporting\Services\AbstractReportBuilder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserGrowthReportBuilder extends AbstractReportBuilder
{
    public function identifier(): string
    {
        return 'user_growth';
    }

    public function title(): string
    {
        return 'User Growth Report';
    }

    public function description(): string
    {
        return 'Daily user registration statistics showing growth over time';
    }

    public static function filtersSchema(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',
        ];
    }

    protected function getDefaultFilters(): array
    {
        return $this->getDefaultDateRange();
    }

    public function build(array $filters = []): ReportResult
    {
        $filters = $this->prepareFilters($filters);

        $query = User::select([
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as new_users'),
        ])
        ->whereBetween('created_at', [$filters['date_from'], $filters['date_to']])
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date');

        $results = $query->get();

        $rows = $results->map(function ($row) {
            return [
                'date' => $row->date,
                'new_users' => (int) $row->new_users,
            ];
        })->toArray();

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'new_users', 'label' => 'New Users', 'type' => 'integer'],
        ];

        $meta = [
            'filters_applied' => $filters,
            'date_range' => [
                'from' => $filters['date_from']->format('Y-m-d'),
                'to' => $filters['date_to']->format('Y-m-d'),
            ],
            'total_new_users' => array_sum(array_column($rows, 'new_users')),
        ];

        return $this->buildResult($rows, $columns, $meta);
    }
}