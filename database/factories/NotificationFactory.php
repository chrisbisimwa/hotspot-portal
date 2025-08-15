<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'channel' => NotificationChannel::SMS->value,
            'to' => fake()->phoneNumber(),
            'message' => fake()->sentence(),
            'status' => NotificationStatus::QUEUED->value,
            'sent_at' => null,
            'failed_at' => null,
            'error_message' => null,
            'provider_response' => null,
            'user_id' => null,
            'order_id' => null,
        ];
    }
}
