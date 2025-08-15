<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        // Users can view their own orders
        return true;
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // Users can only view their own orders
        return $user->id === $order->user_id;
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        // Active users can create orders
        return $user->status === 'active';
    }

    /**
     * Determine whether the user can view all orders (admin only).
     */
    public function adminViewAll(User $user): bool
    {
        return $user->hasRole('admin');
    }
}