<?php

declare(strict_types=1);

namespace App\Livewire\Admin\UserProfiles;

use App\Models\UserProfile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;

class InlineActiveToggle extends Component
{
    public int $profileId;
    public bool $isActive;
    public bool $loading = false;
    public ?string $errorMessage = null;

    protected $listeners = [
        // Si plus tard on veut forcer un refresh externe
    ];

    public function mount(int $profileId, bool $isActive): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $this->profileId = $profileId;
        $this->isActive = $isActive;
    }

    public function toggle(): void
    {
        $this->errorMessage = null;
        $this->loading = true;

        // Optimistic UI
        $newValue = !$this->isActive;
        $previous = $this->isActive;
        $this->isActive = $newValue;

        try {
            $profile = UserProfile::findOrFail($this->profileId);
            $profile->is_active = $newValue;
            $profile->save();

            // Option: émettre un événement si la liste a besoin de réagir globalement
            $this->dispatch('user-profile-updated', id: $profile->id);
        } catch (ModelNotFoundException $e) {
            $this->isActive = $previous;
            $this->errorMessage = 'Profile introuvable.';
        } catch (\Throwable $e) {
            $this->isActive = $previous;
            $this->errorMessage = 'Erreur: '.$e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.user-profiles.inline-active-toggle');
    }
}