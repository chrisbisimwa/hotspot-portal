<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use Livewire\Component;

class MetricCard extends Component
{
    public string $title;
    public string $value;
    public ?string $icon = null;
    public ?array $diff = null;
    public string $color = 'primary';

    public function mount(
        string $title,
        string $value,
        ?string $icon = null,
        ?array $diff = null,
        string $color = 'primary'
    ): void {
        $this->title = $title;
        $this->value = $value;
        $this->icon = $icon;
        $this->diff = $diff;
        $this->color = $color;
    }

    public function render()
    {
        return view('livewire.shared.metric-card');
    }
}