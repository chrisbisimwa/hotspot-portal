<?php
declare(strict_types=1);

namespace App\Livewire\Admin\Orders;

use Livewire\Component;
use App\Models\Order;
use App\Models\User;
use App\Models\UserProfile;

class CreateOrder extends Component
{
    // Champs du formulaire
    public ?int $user_id = null;
    public ?int $user_profile_id = null;
    public int $quantity = 1;
    public float $unit_price = 1.00;

    // Données de sélection
    public array $users = [];
    public array $profiles = [];

    // UI
    public bool $showModal = false;
    public bool $loadingProfiles = false;

    protected $listeners = [
        // Si besoin de réagir à d'autres événements plus tard
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        // Pré-charger un petit lot d'utilisateurs (adapter selon volumétrie)
        $this->users = User::orderBy('name')
            ->limit(100)
            ->get(['id', 'name', 'email'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'label' => $u->name . ' (' . $u->email . ')',
            ])->toArray();
    }

    public function updatedUserId(): void
    {
        $this->user_profile_id = null;
        $this->profiles = [];
        if ($this->user_id) {
            $this->loadingProfiles = true;
            $this->profiles = UserProfile::where('user_id', $this->user_id)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($p) => ['id' => $p->id, 'label' => $p->name])
                ->toArray();
            $this->loadingProfiles = false;
        }
    }

    protected function rules(): array
    {
        return [
            'user_id'         => ['required', 'exists:users,id'],
            'user_profile_id' => ['required', 'exists:user_profiles,id'],
            'quantity'        => ['required', 'integer', 'min:1'],
            'unit_price'      => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'user_id.required'         => 'Utilisateur obligatoire',
            'user_profile_id.required' => 'Profil obligatoire',
            'quantity.required'        => 'Quantité obligatoire',
            'quantity.min'             => 'Quantité doit être >= 1',
            'unit_price.required'      => 'Prix unitaire obligatoire',
            'unit_price.min'           => 'Prix unitaire doit être >= 0',
        ];
    }

    public function open(): void
    {
        $this->resetValidation();
        $this->showModal = true;
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $this->validate();

        $total = $this->quantity * $this->unit_price;

        $order = Order::create([
            'user_id'         => $this->user_id,
            'user_profile_id' => $this->user_profile_id,
            'quantity'        => $this->quantity,
            'unit_price'      => $this->unit_price,
            'total_amount'    => $total,
            'status'          => 'pending',
            'requested_at'    => now(),
        ]);

        // Événement pour rafraîchir la liste (parent ListOrders écoute 'order-created')
        $this->dispatch('order-created', id: $order->id);

        // Optionnel : message flash Livewire (si tu utilises un système de toast)
        session()->flash('success', "Order #{$order->id} created.");

        // Reset partiel
        $this->reset(['quantity', 'unit_price', 'user_profile_id']);
        $this->quantity = 1;
        $this->unit_price = 1.00;

        $this->close();
    }


    public function render()
    {
        return view('livewire.admin.orders.create-order');
    }
}
