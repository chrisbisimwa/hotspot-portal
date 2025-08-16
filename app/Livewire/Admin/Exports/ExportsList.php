<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Exports;

use App\Models\Export;
use Livewire\Component;
use Livewire\WithPagination;

class ExportsList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public function mount(): void
    {
        // Check authorization
        abort_unless(auth()->user()->hasRole('admin'), 403, 'Unauthorized access to exports');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function deleteExport(int $exportId): void
    {
        $export = Export::find($exportId);
        
        if (!$export) {
            session()->flash('error', 'Export not found.');
            return;
        }

        // Delete file if it exists
        if ($export->file_path) {
            \Storage::disk(config('exports.storage_disk', 'local'))->delete($export->file_path);
        }

        $export->delete();
        session()->flash('message', 'Export deleted successfully.');
    }

    public function getExportsProperty()
    {
        $query = Export::with('requestedBy')
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where('report_key', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query->paginate(15);
    }

    public function getStatusOptionsProperty(): array
    {
        return [
            '' => 'All Statuses',
            'queued' => 'Queued',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
        ];
    }

    public function render()
    {
        return view('livewire.admin.exports.exports-list')
            ->title('Exports - Admin Dashboard');
    }
}