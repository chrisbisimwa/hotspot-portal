<?php

declare(strict_types=1);

namespace App\Livewire\Admin\HotspotSessions;

use App\Livewire\Shared\DataTable;
use App\Models\HotspotSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SyncActiveSessionsJob;

class ListHotspotSessions extends DataTable
{
    // Filtres
    public string $statusFilter = '';     // '', active, closed
    public string $interfaceFilter = '';
    public ?int $userFilter = null;

    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    // KPIs
    public int $kpiActive = 0;
    public int $kpiTotalSessions = 0;
    public int $kpiUploadMb = 0;
    public int $kpiDownloadMb = 0;

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'start_time'],
        'sortDirection' => ['except' => 'desc'],
        'perPage'       => ['except' => 25],

        'statusFilter'   => ['except' => ''],
        'interfaceFilter'=> ['except' => ''],
        'userFilter'     => ['except' => null],
        'dateFrom'       => ['except' => null],
        'dateTo'         => ['except' => null],
    ];

    protected $listeners = [
        'hotspot-user-created' => '$refresh',
        'hotspot-user-updated' => '$refresh',
        'hotspot-user-password-reset' => '$refresh',
        'sessions-synced' => '$refresh',
    ];

    public function mount(...$params): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        parent::mount(
            columns: [
                ['field' => 'hotspotUser.username', 'label' => 'Username', 'sortable' => true, 'type' => 'plain'],
                ['field' => 'owner.name',            'label' => 'Owner',    'sortable' => true, 'type' => 'plain'],
                ['field' => 'hotspotUser.userProfile.name', 'label' => 'Profil', 'sortable' => false, 'type' => 'plain'],
                ['field' => 'start_time',            'label' => 'Start',    'sortable' => true, 'type' => 'date'],
                ['field' => 'stop_time',             'label' => 'Stop',     'sortable' => true, 'type' => 'date_nullable'],
                ['field' => 'session_time',          'label' => 'Durée',    'sortable' => false, 'type' => 'duration_computed'],
                ['field' => 'upload_mb',             'label' => 'Up (MB)',  'sortable' => true, 'type' => 'data_mb'],
                ['field' => 'download_mb',           'label' => 'Down (MB)','sortable' => true, 'type' => 'data_mb'],
                ['field' => 'total_mb',              'label' => 'Total (MB)','sortable' => false, 'type' => 'total_mb_computed'],
                ['field' => 'ip_address',            'label' => 'IP',       'sortable' => false, 'type' => 'mono'],
                ['field' => 'mac_address',           'label' => 'MAC',      'sortable' => false, 'type' => 'mono'],
                ['field' => 'interface',             'label' => 'IF',       'sortable' => true, 'type' => 'mono'],
                ['type'  => 'actions',               'label' => 'Actions',  'sortable' => false,
                    'actions_view' => 'livewire.admin.hotspot-sessions.partials.actions'],
            ],
            sortField: 'start_time',
            sortDirection: 'desc',
            perPage: 25,
            searchPlaceholder: 'Search username / owner / IP / MAC...'
        );

        $this->filters['statusFilter']    = &$this->statusFilter;
        $this->filters['interfaceFilter'] = &$this->interfaceFilter;
        $this->filters['userFilter']      = &$this->userFilter;
        $this->filters['dateFrom']        = &$this->dateFrom;
        $this->filters['dateTo']          = &$this->dateTo;
    }

    protected function getQuery(): Builder
    {
        return HotspotSession::query()
            ->with([
                'hotspotUser:id,username,user_profile_id,owner_id',
                'hotspotUser.userProfile:id,name',
                'hotspotUser.owner:id,name,email'
            ]);
    }

    protected function applySearch(Builder $query): void
    {
        if ($this->search === '') {
            return;
        }
        $s = $this->search;
        $query->where(function ($q) use ($s) {
            $q->whereHas('hotspotUser', fn($hu) => $hu->where('username','like',"%$s%"))
              ->orWhereHas('hotspotUser.owner', fn($o) => $o->where('name','like',"%$s%")
                                                            ->orWhere('email','like',"%$s%"))
              ->orWhere('ip_address','like',"%$s%")
              ->orWhere('mac_address','like',"%$s%");
        });
    }

    protected function applyFilter(Builder $query, string $filter, $value): void
    {
        if ($filter === 'statusFilter' && $value !== '') {
            if ($value === 'active') {
                $query->whereNull('stop_time');
            } elseif ($value === 'closed') {
                $query->whereNotNull('stop_time');
            }
        }

        if ($filter === 'interfaceFilter' && $value !== '') {
            $query->where('interface', $value);
        }

        if ($filter === 'userFilter' && $value) {
            $query->where('hotspot_user_id', (int) $value);
        }

        if ($filter === 'dateFrom' && $value) {
            $query->where('start_time', '>=', Carbon::parse($value)->startOfDay());
        }

        if ($filter === 'dateTo' && $value) {
            $query->where('start_time', '<=', Carbon::parse($value)->endOfDay());
        }
    }

    protected function transformRow($row): array
    {
        // Ajout de colonnes calculées
        $durationSeconds = $this->computeDuration($row);
        $totalMb = (int)($row->upload_mb + $row->download_mb);

        return [
            'id' => $row->id,
            'hotspotUser' => $row->hotspotUser,
            'owner' => $row->hotspotUser->owner ?? null,
            'start_time' => $row->start_time,
            'stop_time' => $row->stop_time,
            'session_time' => $durationSeconds,
            'upload_mb' => $row->upload_mb,
            'download_mb' => $row->download_mb,
            'total_mb' => $totalMb,
            'ip_address' => $row->ip_address,
            'mac_address' => $row->mac_address,
            'interface' => $row->interface,
        ];
    }

    private function computeDuration(HotspotSession $s): int
    {
        $start = $s->start_time;
        if (!$start) {
            return 0;
        }
        $end = $s->stop_time ?? now();
        return $end->diffInSeconds($start);
    }

    public function resync(): void
    {
        // Dispatch simple (tu peux utiliser dispatchSync si tu veux forcer)
        Bus::dispatch(new SyncActiveSessionsJob());
        $this->dispatch('sessions-synced');
        session()->flash('success', 'Session sync job dispatched.');
    }

    public function clearFilters(): void
    {
        $this->reset([
            'statusFilter','interfaceFilter','userFilter',
            'dateFrom','dateTo','search'
        ]);
        $this->resetPage();
    }

    public function updated($name, $value): void
    {
        // si besoin de recalcul KPI plus fréquent
    }

    private function computeKpis(): void
    {
        $base = $this->getFilteredBaseQuery();

        $this->kpiTotalSessions = (clone $base)->count();
        $this->kpiActive = (clone $base)->whereNull('stop_time')->count();

        $aggregate = (clone $base)
            ->selectRaw('COALESCE(SUM(upload_mb),0) as up, COALESCE(SUM(download_mb),0) as down')
            ->first();

        $this->kpiUploadMb = (int) ($aggregate->up ?? 0);
        $this->kpiDownloadMb = (int) ($aggregate->down ?? 0);
    }

    private function getFilteredBaseQuery(): Builder
    {
        $query = $this->getQuery();
        $this->applySearch($query);
        foreach ($this->filters as $filter => &$value) {
            $this->applyFilter($query, $filter, $value);
        }
        return $query;
    }

    public function render()
    {
        // Data
        $data = $this->getData();

        // KPIs
        $this->computeKpis();

        // Interfaces distinctes pour le select (limité)
        $interfaces = HotspotSession::query()
            ->select('interface')
            ->whereNotNull('interface')
            ->distinct()
            ->orderBy('interface')
            ->pluck('interface')
            ->take(50)
            ->toArray();

        return view('livewire.admin.hotspot-sessions.list-hotspot-sessions', [
            'data' => $data,
            'interfaces' => $interfaces,
        ])->layout('layouts.admin');
    }
}