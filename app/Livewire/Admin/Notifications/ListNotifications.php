<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Notifications;

use App\Livewire\Shared\DataTable;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use App\Jobs\DispatchPendingNotificationsJob;

class ListNotifications extends DataTable
{
    public string $statusFilter = '';
    public string $channelFilter = '';
    public ?int $userFilter = null;
    public ?int $orderFilter = null;
    public ?int $hotspotUserFilter = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    // KPIs
    public int $kpiTotal = 0;
    public int $kpiSent = 0;
    public int $kpiFailed = 0;
    public int $kpiPending = 0;
    public ?float $kpiSuccessRate = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 25],
        'statusFilter' => ['except' => ''],
        'channelFilter' => ['except' => ''],
        'userFilter' => ['except' => null],
        'orderFilter' => ['except' => null],
        'hotspotUserFilter' => ['except' => null],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
    ];

    protected $listeners = [
        'notification-updated' => '$refresh',
        'notification-resend' => '$refresh',
        'notification-marked-read' => '$refresh',
        'notifications-dispatched' => '$refresh',
    ];

    public function mount(...$params): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        parent::mount(
            columns: [
                ['field' => 'id',               'label' => '#',        'sortable' => true,  'type' => 'plain'],
                ['field' => 'user.name',        'label' => 'User',     'sortable' => false, 'type' => 'plain'],
                ['field' => 'channel',          'label' => 'Channel',  'sortable' => true,  'type' => 'channel_badge'],
                ['field' => 'status',           'label' => 'Status',   'sortable' => true,  'type' => 'status_notification'],
                ['field' => 'to',               'label' => 'To',       'sortable' => true,  'type' => 'mono'],
                ['field' => 'subject',          'label' => 'Subject',  'sortable' => false, 'type' => 'short_text'],
                ['field' => 'sent_at',          'label' => 'Sent',     'sortable' => true,  'type' => 'date_nullable'],
                ['field' => 'read_at',          'label' => 'Read',     'sortable' => true,  'type' => 'date_nullable'],
                ['field' => 'created_at',       'label' => 'Created',  'sortable' => true,  'type' => 'date'],
                ['type'  => 'actions',          'label' => 'Actions',  'sortable' => false,
                    'actions_view' => 'livewire.admin.notifications.partials.actions'],
            ],
            sortField: 'created_at',
            sortDirection: 'desc',
            perPage: 25,
            searchPlaceholder: 'Search to / subject / message / user'
        );
    }

    protected function getQuery(): Builder
    {
        return Notification::query()
            ->with(['user:id,name,email'])
            ->with(['order:id,user_id','hotspotUser:id,username']);
    }

    protected function applySearch(Builder $query): void
    {
        if ($this->search === '') {
            return;
        }
        $s = $this->search;
        $query->where(function ($q) use ($s) {
            $q->where('to','like',"%$s%")
              ->orWhere('subject','like',"%$s%")
              ->orWhere('message','like',"%$s%")
              ->orWhereHas('user', fn($u) => $u->where('name','like',"%$s%")
                                              ->orWhere('email','like',"%$s%"));
        });
    }

    protected function applyFilter(Builder $query, string $filter, $value): void
    {
        if ($filter === 'statusFilter' && $value !== '') {
            $query->where('status', $value);
        }
        if ($filter === 'channelFilter' && $value !== '') {
            $query->where('channel', $value);
        }
        if ($filter === 'userFilter' && $value) {
            $query->where('user_id', (int)$value);
        }
        if ($filter === 'orderFilter' && $value) {
            $query->where('order_id', (int)$value);
        }
        if ($filter === 'hotspotUserFilter' && $value) {
            $query->where('hotspot_user_id', (int)$value);
        }
        if ($filter === 'dateFrom' && $value) {
            $query->where('created_at','>=', Carbon::parse($value)->startOfDay());
        }
        if ($filter === 'dateTo' && $value) {
            $query->where('created_at','<=', Carbon::parse($value)->endOfDay());
        }
    }

    protected function transformRow($row): array
    {
        return [
            'id' => $row->id,
            'user' => $row->user,
            'channel' => $row->channel,
            'status' => $row->status,
            'to' => $row->to,
            'subject' => $row->subject,
            'sent_at' => $row->sent_at,
            'read_at' => $row->read_at,
            'created_at' => $row->created_at,
        ];
    }

    public function clearFilters(): void
    {
        $this->reset([
            'statusFilter','channelFilter','userFilter','orderFilter',
            'hotspotUserFilter','dateFrom','dateTo','search'
        ]);
        $this->resetPage();
    }

    private function getFilteredBaseQuery(): Builder
    {
        $q = $this->getQuery();
        $this->applySearch($q);
        foreach ($this->filters as $filter => $value) {
            $this->applyFilter($q, $filter, $value);
        }
        return $q;
    }

    private function computeKpis(): void
    {
        $base = $this->getFilteredBaseQuery();
        $clone = (clone $base);

        $this->kpiTotal = $clone->count();
        $this->kpiSent = (clone $base)->whereNotNull('sent_at')->count();
        $this->kpiFailed = (clone $base)->where('status','failed')->count();
        $this->kpiPending = (clone $base)->whereIn('status',['queued','retrying','pending'])->count();

        $successBase = $this->kpiSent;
        $denom = $this->kpiSent + $this->kpiFailed;
        $this->kpiSuccessRate = $denom > 0 ? round(($successBase / $denom) * 100, 2) : null;
    }

    public function dispatchPending(): void
    {
        Bus::dispatch(new DispatchPendingNotificationsJob(50));
        session()->flash('success','Dispatch job queued.');
        $this->dispatch('notifications-dispatched');
    }

    public function render()
    {
        $data = $this->getData();
        $this->computeKpis();

        $channels = Notification::query()
            ->select('channel')->distinct()->orderBy('channel')->pluck('channel')->toArray();

        return view('livewire.admin.notifications.list-notifications', [
            'data' => $data,
            'channels' => $channels,
        ])->layout('layouts.admin');
    }
}