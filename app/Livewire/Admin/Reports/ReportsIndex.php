<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Reports;

use App\Domain\Reporting\Services\ReportRegistry;
use Livewire\Component;

class ReportsIndex extends Component
{
    private ReportRegistry $registry;

    public function boot(ReportRegistry $registry): void
    {
        $this->registry = $registry;
    }

    public function mount(): void
    {
        // Check authorization
        abort_unless(auth()->user()->hasRole('admin'), 403, 'Unauthorized access to reports');
    }

    public function getReportsProperty(): array
    {
        return $this->registry->metadata();
    }

    public function render()
    {
        return view('livewire.admin.reports.reports-index')
            ->title('Reports - Admin Dashboard');
    }
}