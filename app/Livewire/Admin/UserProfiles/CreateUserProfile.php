<?php

declare(strict_types=1);

namespace App\Livewire\Admin\UserProfiles;

use App\Models\UserProfile;
use Livewire\Component;

class CreateUserProfile extends Component
{
    public bool $showModal = false;

    public string $name = '';
    public ?string $mikrotik_profile = null;
    public float $price = 0.00;
    public int $validity_minutes = 60;
    public ?int $data_limit_mb = null;
    public ?string $description = null;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:100', 'unique:user_profiles,name'],
            'mikrotik_profile' => ['nullable', 'string', 'max:100'],
            'price'            => ['required', 'numeric', 'min:0'],
            'validity_minutes' => ['required', 'integer', 'min:1'],
            'data_limit_mb'    => ['nullable', 'integer', 'min:1'],
            'description'      => ['nullable', 'string', 'max:500'],
            'is_active'        => ['boolean'],
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
        $data = $this->validate();

        $profile = UserProfile::create($data);

        $this->dispatch('user-profile-created', id: $profile->id);
        session()->flash('success', "Profile {$profile->name} created.");

        $this->reset(['name','mikrotik_profile','price','validity_minutes','data_limit_mb','description','is_active']);
        $this->price = 0.00;
        $this->validity_minutes = 60;
        $this->is_active = true;

        $this->close();
    }

    public function render()
    {
        return view('livewire.admin.user-profiles.create-user-profile');
    }
}