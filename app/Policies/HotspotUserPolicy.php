<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HotspotUser;
use App\Models\User;

class HotspotUserPolicy
{
    /**
     * Determine whether the user can view any hotspot users.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the hotspot user.
     */
    public function view(User $user, HotspotUser $hotspotUser): bool
    {
        // Users can only view their own hotspot users
        return $user->id === $hotspotUser->owner_id;
    }

    /**
     * Determine whether the user can view sessions for the hotspot user.
     */
    public function viewSessions(User $user, HotspotUser $hotspotUser): bool
    {
        // Users can only view sessions for their own hotspot users
        return $user->id === $hotspotUser->owner_id;
    }
}