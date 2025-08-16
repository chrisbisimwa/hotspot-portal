<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use Livewire\Component;

class SearchBar extends Component
{
    public string $search = '';
    public string $placeholder = 'Search...';

    public function mount(string $placeholder = 'Search...'): void
    {
        $this->placeholder = $placeholder;
    }

    public function updatedSearch(): void
    {
        $this->dispatch('search-updated', search: $this->search);
    }

    public function render()
    {
        return view('livewire.shared.search-bar');
    }
}