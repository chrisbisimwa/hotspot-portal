<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use App\Services\Settings\SettingsService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class SettingsGroup extends Component
{
    public string $group;
    public array $items = [];
    public array $form = [];
    public bool $saved = false;

    public function mount(string $group): void
    {
        Gate::authorize('viewAdminLogs'); // ou define un gate spécifique ex: manageSettings

        $this->group = $group;
        $this->reload();
    }

    private function reload(): void
    {
        $settings = Setting::where('group', $this->group)->orderBy('key')->get();
        $this->items = $settings->map(fn($s) => [
            'id' => $s->id,
            'key' => $s->key,
            'type' => $s->type,
            'label' => $s->meta['label'] ?? $s->key,
            'description' => $s->meta['description'] ?? null,
            'value' => $s->scalar_value,
        ])->toArray();

        $this->form = [];
        foreach ($this->items as $i) {
            $this->form[$i['key']] = $i['value'];
        }
    }

    public function save(SettingsService $service): void
    {
        foreach ($this->items as $item) {
            $incoming = $this->form[$item['key']] ?? null;
            $service->update($item['key'], $incoming);
        }
        $this->saved = true;
        $this->reload();
        $this->dispatch('settings-saved');
    }

    public function render()
    {
        return view('livewire.admin.settings.group')
            ->layout('layouts.app', ['title' => 'Paramètres - '.$this->group]);
    }
}