<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Hotspot\Contracts\MikrotikApiInterface;
use App\Domain\Hotspot\DTO\MikrotikProfileProvisionData;
use App\Models\UserProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UserProfileObserver
{
    private function shouldProvision(): bool
    {
        return (bool) config('mikrotik.provision_profiles', true);
    }

    private function buildDto(UserProfile $profile): MikrotikProfileProvisionData
    {
        return new MikrotikProfileProvisionData(
            name: $profile->mikrotik_profile,
            rateLimit: $profile->rate_limit,
            sessionTimeout: $profile->session_timeout,
            idleTimeout: $profile->idle_timeout,
            keepaliveTimeout: $profile->keepalive_timeout,
            sharedUsers: $profile->shared_users
        );
    }

    public function created(UserProfile $profile): void
    {
        if (!$this->shouldProvision()) {
            return;
        }

        if (!$profile->mikrotik_profile) {
            $profile->mikrotik_profile = Str::upper(Str::slug($profile->name, '_'));
            $profile->saveQuietly();
        }

        if (!$profile->isSyncEligible()) {
            return;
        }

        try {
            $api = app(MikrotikApiInterface::class);
            $api->createUserProfile($this->buildDto($profile));
            $profile->synced_at = now();
            $profile->sync_error = null;
            $profile->saveQuietly();
        } catch (\Throwable $e) {
            $profile->sync_error = $e->getMessage();
            $profile->saveQuietly();
            Log::error('UserProfileObserver: create sync failed', [
                'id' => $profile->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updated(UserProfile $profile): void
    {
        if (!$this->shouldProvision()) {
            return;
        }

        // Si désactivé on ne supprime pas (politique), on peut décider de remove user profile :
        if (!$profile->isSyncEligible()) {
            return;
        }

        // Déterminer si champs pertinents ont changé
        $dirty = array_intersect(array_keys($profile->getDirty()), [
            'name','rate_limit','session_timeout','idle_timeout',
            'keepalive_timeout','shared_users','mikrotik_profile'
        ]);

        if (empty($dirty)) {
            return;
        }

        try {
            // Si changement de mikrotik_profile (rename), stratégie simple : recréer puis (optionnel) supprimer l’ancien
            if ($profile->wasChanged('mikrotik_profile') && $profile->getOriginal('mikrotik_profile')) {
                // Option : remove l’ancien
                try {
                    app(MikrotikApiInterface::class)->removeUserProfile($profile->getOriginal('mikrotik_profile'));
                } catch (\Throwable $e) {
                    Log::warning('UserProfileObserver: old profile remove failed (rename scenario)', [
                        'old' => $profile->getOriginal('mikrotik_profile'),
                        'error' => $e->getMessage(),
                    ]);
                }
                // On force un create du nouveau
                app(MikrotikApiInterface::class)->createUserProfile($this->buildDto($profile));
            } else {
                app(MikrotikApiInterface::class)
                    ->updateUserProfile($profile->mikrotik_profile, $this->buildDto($profile));
            }

            $profile->synced_at = now();
            $profile->sync_error = null;
            $profile->saveQuietly();
        } catch (\Throwable $e) {
            $profile->sync_error = $e->getMessage();
            $profile->saveQuietly();
            Log::error('UserProfileObserver: update sync failed', [
                'id' => $profile->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function deleted(UserProfile $profile): void
    {
        if (!$this->shouldProvision()) {
            return;
        }

        // Politique : on ne supprime pas automatiquement sur Mikrotik si d'autres users peuvent l'utiliser encore.
        // Activer si souhaité :
        // try {
        //     app(MikrotikApiInterface::class)->removeUserProfile($profile->mikrotik_profile);
        // } catch (\Throwable $e) {
        //     Log::error('UserProfileObserver: delete sync failed', [
        //         'id' => $profile->id,
        //         'error' => $e->getMessage(),
        //     ]);
        // }
    }
}