<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WebhookEndpoint;

class WebhookEndpointPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $user->hasRole('admin');
    }

    public function manage(User $user): bool
    {
        return $user->hasRole('admin');
    }
}