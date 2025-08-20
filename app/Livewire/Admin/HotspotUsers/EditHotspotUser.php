<?php

declare(strict_types=1);

namespace App\Livewire\Admin\HotspotUsers;

use App\Models\HotspotUser;
use App\Models\UserProfile;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditHotspotUser extends Component
{
    public HotspotUser $hotspotUser;

    public string $status;
    public int $validity_minutes;
    public ?int $data_limit_mb;
    public ?int $user_profile_id;

    public array $profiles = [];

    protected function rules(): array
    {
        return [
            'status'          => ['required','in:active,expired,suspended'],
            'validity_minutes'=> ['required','integer','min:1'],
            'data_limit_mb'   => ['nullable','integer','min:1'],
            'user_profile_id' => ['required','integer', Rule::exists('user_profiles','id')],
        ];
    }

    public function mount(HotspotUser $hotspotUser): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $this->hotspotUser = $hotspotUser->load('userProfile');
        $this->status = $hotspotUser->status ?? 'active';
        $this->validity_minutes = $hotspotUser->validity_minutes;
        $this->data_limit_mb = $hotspotUser->data_limit_mb;
        $this->user_profile_id = $hotspotUser->user_profile_id;

        $this->profiles = UserProfile::where('is_active', true)->orderBy('name')->get(['id','name'])->toArray();
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->hotspotUser->update($data);

        session()->flash('success', 'Hotspot user updated.');
        $this->dispatch('hotspot-user-updated');
    }

    public function render()
    {
        return view('livewire.admin.hotspot-users.edit-hotspot-user')
            ->layout('layouts.admin');
    }
}