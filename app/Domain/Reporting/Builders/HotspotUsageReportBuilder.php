<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Builders;

use App\Domain\Reporting\DTO\ReportResult;
use App\Domain\Reporting\Services\AbstractReportBuilder;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;

class HotspotUsageReportBuilder extends AbstractReportBuilder
{
    public function identifier(): string
    {
        return 'hotspot_usage';
    }

    public function title(): string
    {
        return 'Hotspot Usage Report';
    }

    public function description(): string
    {
        return 'Usage statistics by user profile including users created and active sessions';
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

        $query = UserProfile::select([
            'user_profiles.name as user_profile',
            DB::raw('COUNT(DISTINCT hotspot_users.id) as users_created'),
            DB::raw('COUNT(DISTINCT hotspot_sessions.id) as active_sessions'),
            DB::raw('COALESCE(SUM(hotspot_sessions.data_mb), 0) as data_sum_mb'),
        ])
        ->leftJoin('hotspot_users', function ($join) use ($filters) {
            $join->on('user_profiles.id', '=', 'hotspot_users.user_profile_id')
                 ->whereBetween('hotspot_users.created_at', [$filters['date_from'], $filters['date_to']]);
        })
        ->leftJoin('hotspot_sessions', function ($join) use ($filters) {
            $join->on('hotspot_users.id', '=', 'hotspot_sessions.hotspot_user_id')
                 ->whereBetween('hotspot_sessions.created_at', [$filters['date_from'], $filters['date_to']]);
        })
        ->groupBy('user_profiles.id', 'user_profiles.name')
        ->orderBy('user_profiles.name');

        $results = $query->get();

        $rows = $results->map(function ($row) {
            return [
                'user_profile' => $row->user_profile,
                'users_created' => (int) $row->users_created,
                'active_sessions' => (int) $row->active_sessions,
                'data_sum_mb' => number_format((float) $row->data_sum_mb, 2),
            ];
        })->toArray();

        $columns = [
            ['key' => 'user_profile', 'label' => 'User Profile', 'type' => 'string'],
            ['key' => 'users_created', 'label' => 'Users Created', 'type' => 'integer'],
            ['key' => 'active_sessions', 'label' => 'Active Sessions', 'type' => 'integer'],
            ['key' => 'data_sum_mb', 'label' => 'Data Usage (MB)', 'type' => 'number'],
        ];

        $meta = [
            'filters_applied' => $filters,
            'date_range' => [
                'from' => $filters['date_from']->format('Y-m-d'),
                'to' => $filters['date_to']->format('Y-m-d'),
            ],
            'total_users' => array_sum(array_column($rows, 'users_created')),
            'total_sessions' => array_sum(array_column($rows, 'active_sessions')),
            'total_data_mb' => array_sum(array_map('floatval', array_column($rows, 'data_sum_mb'))),
        ];

        return $this->buildResult($rows, $columns, $meta);
    }
}