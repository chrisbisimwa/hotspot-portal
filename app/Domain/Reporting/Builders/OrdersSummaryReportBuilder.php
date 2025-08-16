<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Builders;

use App\Domain\Reporting\DTO\ReportResult;
use App\Domain\Reporting\Services\AbstractReportBuilder;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrdersSummaryReportBuilder extends AbstractReportBuilder
{
    public function identifier(): string
    {
        return 'orders_summary';
    }

    public function title(): string
    {
        return 'Orders Summary Report';
    }

    public function description(): string
    {
        return 'Daily aggregated summary of orders including count, total amount, and average amount';
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

        $query = Order::select([
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as orders_count'),
            DB::raw('SUM(total_amount) as total_amount'),
            DB::raw('AVG(total_amount) as avg_amount'),
        ])
        ->whereBetween('created_at', [$filters['date_from'], $filters['date_to']])
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date');

        $results = $query->get();

        $rows = $results->map(function ($row) {
            return [
                'date' => $row->date,
                'orders_count' => (int) $row->orders_count,
                'total_amount' => number_format((float) $row->total_amount, 2),
                'avg_amount' => number_format((float) $row->avg_amount, 2),
            ];
        })->toArray();

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'orders_count', 'label' => 'Orders Count', 'type' => 'integer'],
            ['key' => 'total_amount', 'label' => 'Total Amount', 'type' => 'currency'],
            ['key' => 'avg_amount', 'label' => 'Average Amount', 'type' => 'currency'],
        ];

        $meta = [
            'filters_applied' => $filters,
            'date_range' => [
                'from' => $filters['date_from']->format('Y-m-d'),
                'to' => $filters['date_to']->format('Y-m-d'),
            ],
        ];

        return $this->buildResult($rows, $columns, $meta);
    }
}