<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class DataTable extends Component
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
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
        'perPage'       => ['except' => 15],
    ];

    public function mount(
        array $columns = [],
        string $sortField = 'id',
        string $sortDirection = 'desc',
        int $perPage = 15,
        string $searchPlaceholder = 'Search...'
    ): void {
        if ($columns) {
            $this->columns = $columns;
        }
        $this->sortField         = $sortField;
        $this->sortDirection     = $sortDirection;
        $this->perPage           = $perPage;
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

    

    abstract protected function getQuery(): Builder;

    public function getData(): LengthAwarePaginator
    {
        $query = $this->getQuery();

        if ($this->search !== '') {
            $this->applySearch($query);
        }

        foreach ($this->filters as $filter => $value) {
            if ($value !== '' && $value !== null) {
                $this->applyFilter($query, $filter, $value);
            }
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    protected function applySearch(Builder $query): void
    {
        // Override dans l'enfant
    }

    protected function applyFilter(Builder $query, string $filter, $value): void
    {
        // Override dans l'enfant
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

    public function exportCsv(): StreamedResponse
    {
        $filename = 'export_' . class_basename(static::class) . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, array_map(
                fn ($c) => $c['label'] ?? $c['field'] ?? 'col',
                $this->columns
            ));

            $query = $this->getQuery();

            if ($this->search !== '') {
                $this->applySearch($query);
            }
            foreach ($this->filters as $filter => $value) {
                if ($value !== '' && $value !== null) {
                    $this->applyFilter($query, $filter, $value);
                }
            }
            $query->orderBy($this->sortField, $this->sortDirection);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    $line = [];
                    foreach ($this->columns as $col) {
                        $type = $col['type'] ?? null;
                        if ($type === 'actions' || $type === 'slot') {
                            $line[] = '';
                        } else {
                            $line[] = data_get($row, $col['field'] ?? '') ?? '';
                        }
                    }
                    fputcsv($handle, $line);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportExcel(): StreamedResponse
    {
        // Placeholder : renvoie un CSV nommé .xlsx
        $response = $this->exportCsv();
        $disposition = $response->headers->get('Content-Disposition');
        $response->headers->set(
            'Content-Disposition',
            preg_replace('/\.csv"/', '.xlsx"', $disposition ?? 'attachment; filename="export.xlsx"')
        );
        return $response;
    }

    public function render()
    {
        // Laisser les enfants override s'ils veulent wrapper.
        return view('livewire.shared.data-table', [
            'data' => $this->getData(),
        ]);
    }
}