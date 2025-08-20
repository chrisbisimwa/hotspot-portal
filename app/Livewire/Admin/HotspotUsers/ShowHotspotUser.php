<?php

declare(strict_types=1);

namespace App\Livewire\Admin\HotspotUsers;

use App\Models\HotspotUser;
use Livewire\Component;

class ShowHotspotUser extends Component
{
    public HotspotUser $hotspotUser;

    public function mount(HotspotUser $hotspotUser): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $this->hotspotUser = $hotspotUser->load(['owner','userProfile','hotspotSessions' => function ($q) {
            $q->latest()->limit(25);
        }]);
    }

    public function refreshData(): void
    {
        $this->hotspotUser->refresh()->load(['owner','userProfile','hotspotSessions' => fn($q) => $q->latest()->limit(25)]);
    }

    public function resetPassword(): void
    {
        $new = str()->random(10);
        $this->hotspotUser->update(['password' => $new]);
        session()->flash('success', 'Password reset.');
        $this->dispatch('hotspot-user-password-reset', id: $this->hotspotUser->id);
    }

    public function forceExpire(): void
    {
        $this->hotspotUser->update([
            'status' => 'expired',
            'expired_at' => now(),
        ]);
        session()->flash('success', 'User forced to expired.');
        $this->refreshData();
    }

    public function markRead(): void
    {
        $this->hotspotUser->update(['read_at' => now()]);
        $this->refreshData();
    }

    public function render()
    {
        return view('livewire.admin.hotspot-users.show-hotspot-user')
            ->layout('layouts.admin');
    }
}