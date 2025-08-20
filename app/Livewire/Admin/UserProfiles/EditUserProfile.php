<?php

declare(strict_types=1);

namespace App\Livewire\Admin\UserProfiles;

use App\Models\UserProfile;
use Livewire\Component;
use Illuminate\Validation\Rule;

class EditUserProfile extends Component
{
    public UserProfile $userProfile;

    public string $name;
    public ?string $mikrotik_profile;
    public float $price;
    public int $validity_minutes;
    public ?int $data_limit_mb;
    public ?string $description;
    public bool $is_active;

    protected function rules(): array
    {
        return [
            'name'             => [
                'required','string','max:100',
                Rule::unique('user_profiles','name')->ignore($this->userProfile->id),
            ],
            'mikrotik_profile' => ['nullable','string','max:100'],
            'price'            => ['required','numeric','min:0'],
            'validity_minutes' => ['required','integer','min:1'],
            'data_limit_mb'    => ['nullable','integer','min:1'],
            'description'      => ['nullable','string','max:500'],
            'is_active'        => ['boolean'],
        ];
    }

    public function mount(UserProfile $userProfile): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $this->userProfile = $userProfile;

        $this->name = $userProfile->name;
        $this->mikrotik_profile = $userProfile->mikrotik_profile;
        $this->price = (float) $userProfile->price;
        $this->validity_minutes = $userProfile->validity_minutes;
        $this->data_limit_mb = $userProfile->data_limit_mb;
        $this->description = $userProfile->description;
        $this->is_active = (bool) $userProfile->is_active;
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->userProfile->update($data);

        session()->flash('success', "Profile {$this->userProfile->name} updated.");
        $this->dispatch('user-profile-updated', id: $this->userProfile->id);
        // Option : redirect to show
        // return redirect()->route('admin.user-profiles.show', $this->userProfile->id);
    }

    public function render()
    {
        return view('livewire.admin.user-profiles.edit-user-profile')
            ->layout('layouts.admin');
    }
}