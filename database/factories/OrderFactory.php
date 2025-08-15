<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $unitPrice = fake()->randomFloat(2, 1, 20);

        return [
            'user_id' => User::factory(),
            'user_profile_id' => UserProfile::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $unitPrice * $quantity,
            'status' => OrderStatus::PENDING->value,
            'payment_reference' => null,
            'requested_at' => now(),
            'paid_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'expires_at' => null,
            'meta' => null,
        ];
    }
}
