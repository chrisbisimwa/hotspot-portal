<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class DataTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 15;
    public array $columns = [];
    public array $filters = [];
    public string $searchPlaceholder = 'Search...';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
    ];

    public function mount(
        array $columns,
        string $sortField = 'id',
        string $sortDirection = 'desc',
        int $perPage = 15,
        string $searchPlaceholder = 'Search...'
    ): void {
        $this->columns = $columns;
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
        $this->perPage = $perPage;
        $this->searchPlaceholder = $searchPlaceholder;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * This method should be overridden in parent components to provide the query
     */
    protected function getQuery(): Builder
    {
        throw new \Exception('getQuery() method must be implemented in the parent component');
    }

    public function getData(): LengthAwarePaginator
    {
        $query = $this->getQuery();

        // Apply search if provided
        if (!empty($this->search)) {
            $this->applySearch($query);
        }

        // Apply filters
        foreach ($this->filters as $filter => $value) {
            if (!empty($value)) {
                $this->applyFilter($query, $filter, $value);
            }
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    /**
     * Apply search to the query - should be overridden in parent components
     */
    protected function applySearch(Builder $query): void
    {
        // Default implementation - should be overridden
    }

    /**
     * Apply filter to the query - should be overridden in parent components
     */
    protected function applyFilter(Builder $query, string $filter, $value): void
    {
        // Default implementation - should be overridden
    }

    public function getSortIcon(string $field): string
    {
        if ($this->sortField !== $field) {
            return 'fas fa-sort text-muted';
        }

        return $this->sortDirection === 'asc' 
            ? 'fas fa-sort-up' 
            : 'fas fa-sort-down';
    }

    public function render()
    {
        return view('livewire.shared.data-table', [
            'data' => $this->getData(),
        ]);
    }
}