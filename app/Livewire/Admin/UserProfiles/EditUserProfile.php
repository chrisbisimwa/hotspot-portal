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
    public ?string $rate_limit;
    public ?string $session_timeout;
    public ?string $idle_timeout;
    public ?string $keepalive_timeout;
    public ?int $shared_users;

    protected function rules(): array
    {
         return [
            'name'             => [
                'required','string','max:100',
                Rule::unique('user_profiles','name')->ignore($this->userProfile->id),
            ],
            'mikrotik_profile' => [
                'nullable','string','max:100',
                Rule::unique('user_profiles','mikrotik_profile')->ignore($this->userProfile->id),
            ],
            'price'            => ['required','numeric','min:0'],
            'validity_minutes' => ['required','integer','min:1'],
            'data_limit_mb'    => ['nullable','integer','min:1'],
            'description'      => ['nullable','string','max:500'],
            'is_active'        => ['boolean'],
            'rate_limit'       => ['nullable','string','max:100'],
            'session_timeout'  => ['nullable','string','max:50'],
            'idle_timeout'     => ['nullable','string','max:50'],
            'keepalive_timeout'=> ['nullable','string','max:50'],
            'shared_users'     => ['nullable','integer','min:1','max:100'],
        ];
    }

    public function mount(UserProfile $userProfile): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $this->userProfile = $userProfile;

       $this->fill($userProfile->only([
            'name','mikrotik_profile','price','validity_minutes','data_limit_mb',
            'description','is_active','rate_limit','session_timeout','idle_timeout',
            'keepalive_timeout','shared_users'
        ]));
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