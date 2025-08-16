<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class MetricsPolicy
{
    /**
     * Determine whether the user can view metrics.
     */
    public function view(User $user): bool
    {
        return $user->hasRole('admin');
    }
}