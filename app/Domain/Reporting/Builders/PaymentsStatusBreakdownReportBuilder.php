<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Builders;

use App\Domain\Reporting\DTO\ReportResult;
use App\Domain\Reporting\Services\AbstractReportBuilder;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentsStatusBreakdownReportBuilder extends AbstractReportBuilder
{
    public function identifier(): string
    {
        return 'payments_status_breakdown';
    }

    public function title(): string
    {
        return 'Payments Status Breakdown';
    }

    public function description(): string
    {
        return 'Breakdown of payments by status with count and total amounts';
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

        $query = Payment::select([
            'status',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(CASE WHEN status = "success" THEN net_amount ELSE 0 END) as total_amount'),
        ])
        ->whereBetween('created_at', [$filters['date_from'], $filters['date_to']])
        ->groupBy('status')
        ->orderBy('status');

        $results = $query->get();

        $rows = $results->map(function ($row) {
            return [
                'status' => ucfirst($row->status ?? 'unknown'),
                'count' => (int) $row->count,
                'total_amount' => number_format((float) $row->total_amount, 2),
            ];
        })->toArray();

        $columns = [
            ['key' => 'status', 'label' => 'Payment Status', 'type' => 'string'],
            ['key' => 'count', 'label' => 'Count', 'type' => 'integer'],
            ['key' => 'total_amount', 'label' => 'Total Amount (Net)', 'type' => 'currency'],
        ];

        $meta = [
            'filters_applied' => $filters,
            'date_range' => [
                'from' => $filters['date_from']->format('Y-m-d'),
                'to' => $filters['date_to']->format('Y-m-d'),
            ],
            'total_payments' => $results->sum('count'),
            'total_net_amount' => $results->sum('total_amount'),
        ];

        return $this->buildResult($rows, $columns, $meta);
    }
}