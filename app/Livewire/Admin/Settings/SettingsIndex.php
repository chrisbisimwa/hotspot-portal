<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Component;

class SettingsIndex extends Component
{
    public array $groups = [];

    public function mount(): void
    {
        $this->groups = Setting::query()
            ->select('group')
            ->groupBy('group')
            ->orderBy('group')
            ->pluck('group')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.settings.settings-index')
            ->layout('layouts.admin', ['title' => 'Paramètres']);
    }
}