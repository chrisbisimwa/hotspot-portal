<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Support\StatusColor;
use Livewire\Component;

class StatusBadge extends Component
{
    public string $status;
    public string $domain;
    public ?string $customColor = null;

    public function mount(string $status, string $domain, ?string $customColor = null): void
    {
        $this->status = $status;
        $this->domain = $domain;
        $this->customColor = $customColor;
    }

    public function getBadgeColorProperty(): string
    {
        return $this->customColor ?? StatusColor::color($this->domain, $this->status);
    }

    public function render()
    {
        return view('livewire.shared.status-badge');
    }
}