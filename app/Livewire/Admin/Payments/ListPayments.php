<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Payments;

use App\Livewire\Shared\DataTable;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ReconcilePaymentsJob; // Si job global – sinon créer un job ciblé

class ListPayments extends DataTable
{
    public string $statusFilter = '';
    public string $providerFilter = '';
    public ?int $userFilter = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?float $amountMin = null;
    public ?float $amountMax = null;

    // KPIs
    public int $kpiCount = 0;
    public float $kpiAmount = 0.0;
    public float $kpiNet = 0.0;
    public ?float $kpiSuccessRate = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 25],

        'statusFilter' => ['except' => ''],
        'providerFilter' => ['except' => ''],
        'userFilter' => ['except' => null],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
        'amountMin' => ['except' => null],
        'amountMax' => ['except' => null],
    ];

    protected $listeners = [
        'payment-updated' => '$refresh',
        'payment-reconciled' => '$refresh',
    ];

    public function mount(...$params): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        parent::mount(
            columns: [
                ['field' => 'id',              'label' => '#',        'sortable' => true,  'type' => 'plain'],
                ['field' => 'user.name',       'label' => 'User',     'sortable' => false, 'type' => 'plain'],
                ['field' => 'order_id',        'label' => 'Order',    'sortable' => true,  'type' => 'plain'],
                ['field' => 'provider',        'label' => 'Provider', 'sortable' => true,  'type' => 'badge_provider'],
                ['field' => 'status',          'label' => 'Status',   'sortable' => true,  'type' => 'status_payment'],
                ['field' => 'amount',          'label' => 'Amount',   'sortable' => true,  'type' => 'money'],
                ['field' => 'net_amount',      'label' => 'Net',      'sortable' => true,  'type' => 'money'],
                ['field' => 'transaction_ref', 'label' => 'Trans Ref','sortable' => true,  'type' => 'mono_trunc'],
                ['field' => 'paid_at',         'label' => 'Paid At',  'sortable' => true,  'type' => 'date_nullable'],
                ['field' => 'created_at',      'label' => 'Created',  'sortable' => true,  'type' => 'date'],
                ['type'  => 'actions',         'label' => 'Actions',  'sortable' => false,
                    'actions_view' => 'livewire.admin.payments.partials.actions'],
            ],
            sortField: 'created_at',
            sortDirection: 'desc',
            perPage: 25,
            searchPlaceholder: 'Search ref / internal / user / email'
        );
    }

    protected function getQuery(): Builder
    {
        return Payment::query()
            ->with(['user:id,name,email', 'order:id,user_id,total_amount']);
    }

    protected function applySearch(Builder $query): void
    {
        if ($this->search === '') {
            return;
        }
        $s = $this->search;
        $query->where(function ($q) use ($s) {
            $q->where('transaction_ref', 'like', "%$s%")
              ->orWhere('internal_ref', 'like', "%$s%")
              ->orWhereHas('user', fn($u) => $u->where('name','like',"%$s%")
                                               ->orWhere('email','like',"%$s%"));
        });
    }

    protected function applyFilter(Builder $query, string $filter, $value): void
    {
        if ($filter === 'statusFilter' && $value !== '') {
            $query->where('status', $value);
        }
        if ($filter === 'providerFilter' && $value !== '') {
            $query->where('provider', $value);
        }
        if ($filter === 'userFilter' && $value) {
            $query->where('user_id', (int)$value);
        }
        if ($filter === 'dateFrom' && $value) {
            $query->where('created_at', '>=', Carbon::parse($value)->startOfDay());
        }
        if ($filter === 'dateTo' && $value) {
            $query->where('created_at', '<=', Carbon::parse($value)->endOfDay());
        }
        if ($filter === 'amountMin' && $value !== null && $value !== '') {
            $query->where('amount', '>=', (float)$value);
        }
        if ($filter === 'amountMax' && $value !== null && $value !== '') {
            $query->where('amount', '<=', (float)$value);
        }
    }

    protected function transformRow($row): array
    {
        return [
            'id' => $row->id,
            'user' => $row->user,
            'order_id' => $row->order_id,
            'provider' => $row->provider,
            'status' => $row->status,
            'amount' => (float) $row->amount,
            'net_amount' => (float) $row->net_amount,
            'transaction_ref' => $row->transaction_ref,
            'paid_at' => $row->paid_at,
            'created_at' => $row->created_at,
        ];
    }

    public function clearFilters(): void
    {
        $this->reset([
            'statusFilter','providerFilter','userFilter','dateFrom','dateTo',
            'amountMin','amountMax','search'
        ]);
        $this->resetPage();
    }

    public function reconcileAll(): void
    {
        // Job global (déjà existant ReconcilePaymentsJob). Pour un ciblé, créer un job spécifique.
        Bus::dispatch(new ReconcilePaymentsJob());
        session()->flash('success', 'Reconcile job dispatched.');
    }

    private function computeKpis(): void
    {
        $base = $this->getFilteredBaseQuery();

        $clone = (clone $base);
        $this->kpiCount = $clone->count();

        $sums = (clone $base)
            ->selectRaw('COALESCE(SUM(amount),0) as sum_amount, COALESCE(SUM(net_amount),0) as sum_net')
            ->first();
        $this->kpiAmount = (float) $sums->sum_amount;
        $this->kpiNet = (float) $sums->sum_net;

        $success = (clone $base)->where('status','success')->count();
        $failed = (clone $base)->where('status','failed')->count();
        $totalForRate = $success + $failed;
        $this->kpiSuccessRate = $totalForRate > 0 ? round(($success / $totalForRate) * 100, 2) : null;
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

    public function render()
    {
        $data = $this->getData();
        $this->computeKpis();

        $providers = Payment::query()
            ->select('provider')
            ->distinct()
            ->orderBy('provider')
            ->pluck('provider')
            ->toArray();

        return view('livewire.admin.payments.list-payments', [
            'data' => $data,
            'providers' => $providers,
        ])->layout('layouts.admin');
    }
}