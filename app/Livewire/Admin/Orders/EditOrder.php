<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Component;

class EditOrder extends Component
{
    public Order $order;

    // Form fields
    public int $quantity;
    public float $unit_price;
    public string $status;
    public ?int $user_profile_id;
    public ?string $payment_reference;

    // For UI
    public array $availableStatuses = [];

    public function mount(Order $order): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $this->order = $order->load(['user', 'userProfile']);
        $this->quantity = $order->quantity;
        $this->unit_price = (float) $order->unit_price;
        $this->status = $order->status ?? 'pending';
        $this->user_profile_id = $order->user_profile_id;
        $this->payment_reference = $order->payment_reference;
        $this->availableStatuses = array_map(
            fn (OrderStatus $s) => $s->value,
            OrderStatus::cases()
        );
    }

    protected function rules(): array
    {
        return [
            'quantity'          => ['required', 'integer', 'min:1'],
            'unit_price'        => ['required', 'numeric', 'min:0'],
            'status'            => ['required', 'in:' . implode(',', $this->availableStatuses)],
            'user_profile_id'   => ['required', 'exists:user_profiles,id'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function updatedStatus(): void
    {
        // Rien pour l’instant : on peut prévisualiser ici si besoin
    }

    private function applyStatusTimestamps(Order $order, string $oldStatus, string $newStatus): void
    {
        if ($oldStatus !== $newStatus) {
            if ($newStatus === OrderStatus::PAYMENT_RECEIVED->value && !$order->paid_at) {
                $order->paid_at = now();
            }
            if ($newStatus === OrderStatus::COMPLETED->value && !$order->completed_at) {
                $order->completed_at = now();
            }
            if ($newStatus === OrderStatus::CANCELLED->value && !$order->cancelled_at) {
                $order->cancelled_at = now();
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $oldStatus = $this->order->status ?? 'pending';

        $this->order->quantity = $this->quantity;
        $this->order->unit_price = $this->unit_price;
        $this->order->total_amount = $this->quantity * $this->unit_price;
        $this->order->status = $this->status;
        $this->order->user_profile_id = $this->user_profile_id;
        $this->order->payment_reference = $this->payment_reference;

        $this->applyStatusTimestamps($this->order, $oldStatus, $this->status);

        $this->order->save();

        session()->flash('success', "Order #{$this->order->id} updated.");
        $this->dispatch('order-updated', id: $this->order->id);

        // Optionnel : redirection vers show
        // return redirect()->route('admin.orders.show', $this->order->id);
    }

    public function render()
    {
        return view('livewire.admin.orders.edit-order')
            ->layout('layouts.admin');
    }
}