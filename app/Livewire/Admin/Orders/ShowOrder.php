<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Livewire\Component;

class ShowOrder extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $this->order->load(['user', 'userProfile', 'payments']);
    }

    public function refreshData(): void
    {
        $this->order->refresh()->load(['user', 'userProfile', 'payments']);
    }


     public function render()
    {
        return view('livewire.admin.orders.show-order')
            ->layout('layouts.admin');
    }
}
