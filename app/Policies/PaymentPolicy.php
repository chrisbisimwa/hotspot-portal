<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        // Users can only view their own payments
        return $user->id === $payment->user_id;
    }

    /**
     * Determine whether the user can view all payments (admin only).
     */
    public function adminViewAll(User $user): bool
    {
        return $user->hasRole('admin');
    }
}