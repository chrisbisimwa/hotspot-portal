<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Logs;

use App\Models\Log;
use App\Models\User;
use App\Enums\LogLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LogsViewer extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public array $levels = [];

    #[Url]
    public ?string $action = null;

    #[Url]
    public ?string $actorId = null;

    #[Url]
    public ?string $loggableType = null;

    #[Url]
    public ?string $dateFrom = null;

    #[Url]
    public ?string $dateTo = null;

    #[Url]
    public bool $includeDeleted = false;

    #[Url]
    public bool $onlyDeleted = false;

    #[Url]
    public int $perPage = 25;

    public array $selected = [];

    public ?Log $detailLog = null;
    public bool $showDetailModal = false;

    protected $queryString = [
        'search','levels','action','actorId','loggableType','dateFrom','dateTo',
        'includeDeleted','onlyDeleted','perPage'
    ];

    protected $listeners = [
        'refreshLogs' => '$refresh',
    ];

    public function mount(): void
    {
        if (!$this->dateFrom) {
            $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        }
        if (!$this->dateTo) {
            $this->dateTo = now()->format('Y-m-d');
        }

        // Normaliser niveaux (enum -> lower)
        $this->levels = array_values(array_filter(array_map(
            fn($l) => is_string($l) ? strtolower($l) : $l,
            $this->levels
        )));
    }

    public function updatedOnlyDeleted(): void
    {
        if ($this->onlyDeleted) {
            $this->includeDeleted = true;
        }
    }

    public function toggleLevel(string $level): void
    {
        $level = strtolower($level);
        if (in_array($level, $this->levels, true)) {
            $this->levels = array_values(array_diff($this->levels, [$level]));
        } else {
            $this->levels[] = $level;
        }
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->levels = [];
        $this->action = null;
        $this->actorId = null;
        $this->loggableType = null;
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->includeDeleted = false;
        $this->onlyDeleted = false;
        $this->selected = [];
        $this->resetPage();
    }

    private function baseQuery(): Builder
    {
        $query = Log::query()
            ->with(['actor'])
            ->when($this->levels, fn($q) => $q->whereIn('level', $this->levels))
            ->when($this->action, fn($q) => $q->where('action', $this->action))
            ->when($this->actorId, fn($q) => $q->where('actor_id', $this->actorId))
            ->when($this->loggableType, fn($q) => $q->where('loggable_type', $this->loggableType))
            ->when($this->dateFrom, fn($q) => $q->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($this->dateTo, fn($q) => $q->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay()))
            ->when($this->search, function($q) {
                $term = '%'.$this->search.'%';
                $q->where(function($inner) use ($term) {
                    $inner->where('message','like',$term)
                          ->orWhere('action','like',$term);
                });
            });

        if ($this->onlyDeleted) {
            $query->onlyTrashed();
        } elseif ($this->includeDeleted) {
            $query->withTrashed();
        }

        return $query;
    }

    public function getLogsProperty()
    {
        return $this->baseQuery()
            ->latest('id')
            ->paginate($this->perPage);
    }

    public function getActionsProperty(): array
    {
        return Cache::remember('distinct_log_actions', 300, function () {
            return Log::query()
                ->select('action')
                ->whereNotNull('action')
                ->groupBy('action')
                ->orderBy('action')
                ->pluck('action')
                ->toArray();
        });
    }

    public function getDistinctLoggableTypesProperty(): array
    {
        return Cache::remember('distinct_log_types', 300, function () {
            return Log::query()
                ->select('loggable_type')
                ->whereNotNull('loggable_type')
                ->groupBy('loggable_type')
                ->orderBy('loggable_type')
                ->pluck('loggable_type')
                ->toArray();
        });
    }

    public function getLevelOptionsProperty(): array
    {
        // Si enum disponible
        if (enum_exists(LogLevel::class)) {
            return array_map(fn($c) => strtolower($c->value), LogLevel::cases());
        }
        return ['debug','info','notice','warning','error','critical','alert','emergency'];
    }

    public function updated($name): void
    {
        if ($name !== 'selected') {
            $this->resetPage();
        }
    }

    public function toggleSelectAllVisible(): void
    {
        $idsVisible = $this->logs->pluck('id')->map(fn($i) => (string)$i)->toArray();
        $allSelected = empty(array_diff($idsVisible, $this->selected));
        if ($allSelected) {
            // Unselect
            $this->selected = array_values(array_diff($this->selected, $idsVisible));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $idsVisible)));
        }
    }

    public function openDetail(int $id): void
    {
        $this->detailLog = $this->baseQuery()->where('id', $id)->first();
        $this->showDetailModal = $this->detailLog !== null;
    }

    public function closeDetail(): void
    {
        $this->detailLog = null;
        $this->showDetailModal = false;
    }

    public function bulkAction(string $action): void
    {
        if (empty($this->selected)) {
            return;
        }

        $query = Log::query()->whereIn('id', $this->selected);

        switch ($action) {
            case 'delete':
                $query->delete();
                break;
            case 'restore':
                $query->onlyTrashed()->restore();
                break;
            case 'force-delete':
                $query->withTrashed()->forceDelete();
                break;
        }

        $this->selected = [];
        $this->dispatch('refreshLogs');
    }

    public function restore(int $id): void
    {
        Log::withTrashed()->where('id', $id)->restore();
        $this->dispatch('refreshLogs');
        $this->openDetail($id);
    }

    public function forceDelete(int $id): void
    {
        Log::withTrashed()->where('id',$id)->forceDelete();
        $this->closeDetail();
        $this->dispatch('refreshLogs');
    }

    public function export(): \Symfony\Component\HttpFoundation\Response
    {
        // On redirige vers la route export avec les mêmes filtres (GET)
        $params = [
            'levels' => $this->levels,
            'action' => $this->action,
            'actorId' => $this->actorId,
            'loggableType' => $this->loggableType,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'search' => $this->search,
            'includeDeleted' => $this->includeDeleted ? 1 : 0,
            'onlyDeleted' => $this->onlyDeleted ? 1 : 0,
        ];
        return redirect()->route('admin.logs.export', $params);
    }

    public function render()
    {
        $stats = $this->buildStats();

        return view('livewire.admin.logs.logs-viewer', [
            'logsPage' => $this->logs,
            'actionsList' => $this->actions,
            'typesList' => $this->distinct_loggable_types,
            'levelOptions' => $this->levelOptions,
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title' => 'Logs',
        ]);
    }

    private function buildStats(): array
    {
        // Petit résumé (limité pour éviter surcharge)
        $clone = clone $this->baseQuery();
        $total = (clone $clone)->count();
        $byLevel = (clone $clone)
            ->select('level', DB::raw('count(*) as c'))
            ->groupBy('level')
            ->pluck('c','level')
            ->toArray();

        return [
            'total' => $total,
            'by_level' => $byLevel,
        ];
    }
}