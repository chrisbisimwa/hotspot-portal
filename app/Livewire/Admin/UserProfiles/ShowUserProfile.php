<?php

declare(strict_types=1);

namespace App\Livewire\Admin\UserProfiles;

use App\Models\UserProfile;
use Livewire\Component;

class ShowUserProfile extends Component
{
    public UserProfile $userProfile;

    public function mount(UserProfile $userProfile): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $this->userProfile = $userProfile;
    }

    public function refreshData(): void
    {
        $this->userProfile->refresh();
    }

    public function render()
    {
        return view('livewire.admin.user-profiles.show-user-profile')
            ->layout('layouts.admin');
    }
}