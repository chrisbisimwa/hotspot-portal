<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use Livewire\Component;

class ShowPayment extends Component
{
    public Payment $payment;

    public bool $showRaw = false;

    public function mount(Payment $payment): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $this->payment = $payment->load(['user','order']);
    }

    public function toggleRaw(): void
    {
        $this->showRaw = !$this->showRaw;
    }

    public function render()
    {
        return view('livewire.admin.payments.show-payment')
            ->layout('layouts.admin');
    }
}