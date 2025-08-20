<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use Livewire\Component;
use Illuminate\Validation\Rule;

class EditPayment extends Component
{
    public Payment $payment;

    public string $status;
    public ?string $transaction_ref;
    public ?string $internal_ref;
    public ?string $provider;
    public ?float $fee_amount;
    public ?float $net_amount;
    public ?string $paid_at;
    public ?string $confirmed_at;
    public ?string $refunded_at;

    public array $availableStatuses = [];
    public array $availableProviders = ['serdipay','cash']; // adapter

    public function mount(Payment $payment): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $this->payment = $payment;
        $this->status = $payment->status ?? 'pending';
        $this->transaction_ref = $payment->transaction_ref;
        $this->internal_ref = $payment->internal_ref;
        $this->provider = $payment->provider;
        $this->fee_amount = $payment->fee_amount !== null ? (float)$payment->fee_amount : null;
        $this->net_amount = $payment->net_amount !== null ? (float)$payment->net_amount : null;
        $this->paid_at = $payment->paid_at?->format('Y-m-d H:i');
        $this->confirmed_at = $payment->confirmed_at?->format('Y-m-d H:i');
        $this->refunded_at = $payment->refunded_at?->format('Y-m-d H:i');

        // Enum PaymentStatus : adapter dynamiquement si besoin
        $this->availableStatuses = [
            'pending','initiated','processing','success','failed','cancelled','refunded'
        ];
    }

    protected function rules(): array
    {
        return [
            'status' => ['required', Rule::in($this->availableStatuses)],
            'transaction_ref' => ['nullable','string','max:120'],
            'internal_ref' => ['nullable','string','max:120'],
            'provider' => ['required', Rule::in($this->availableProviders)],
            'fee_amount' => ['nullable','numeric','min:0'],
            'net_amount' => ['nullable','numeric','min:0'],
            'paid_at' => ['nullable','date'],
            'confirmed_at' => ['nullable','date'],
            'refunded_at' => ['nullable','date'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->payment->status = $this->status;
        $this->payment->transaction_ref = $this->transaction_ref;
        $this->payment->internal_ref = $this->internal_ref;
        $this->payment->provider = $this->provider;
        if ($this->fee_amount !== null) {
            $this->payment->fee_amount = $this->fee_amount;
        }
        if ($this->net_amount !== null) {
            $this->payment->net_amount = $this->net_amount;
        }
        $this->payment->paid_at = $this->paid_at ? \Carbon\Carbon::parse($this->paid_at) : null;
        $this->payment->confirmed_at = $this->confirmed_at ? \Carbon\Carbon::parse($this->confirmed_at) : null;
        $this->payment->refunded_at = $this->refunded_at ? \Carbon\Carbon::parse($this->refunded_at) : null;

        $this->payment->save();

        session()->flash('success', "Payment #{$this->payment->id} updated.");
        $this->dispatch('payment-updated', id: $this->payment->id);
    }

    public function render()
    {
        return view('livewire.admin.payments.edit-payment')
            ->layout('layouts.admin');
    }
}