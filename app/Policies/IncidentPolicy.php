<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

class IncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Incident $incident): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Incident $incident): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Incident $incident): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Incident $incident): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Incident $incident): bool
    {
        return $user->hasRole('admin');
    }
}