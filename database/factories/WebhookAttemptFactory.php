<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WebhookAttemptStatus;
use App\Models\WebhookAttempt;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebhookAttemptFactory extends Factory
{
    protected $model = WebhookAttempt::class;

    public function definition(): array
    {
        return [
            'webhook_endpoint_id' => WebhookEndpoint::factory(),
            'event_name' => $this->faker->randomElement([
                'PaymentSucceeded',
                'HotspotUserProvisioned',
                'OrderCompleted',
                'IncidentStatusChanged',
            ]),
            'payload' => [
                'event' => $this->faker->word(),
                'data' => [
                    'id' => $this->faker->randomNumber(),
                    'test' => $this->faker->word(),
                ],
                'timestamp' => now()->toISOString(),
            ],
            'response_code' => $this->faker->optional()->randomElement([200, 201, 400, 404, 500]),
            'response_body' => $this->faker->optional()->sentence(),
            'attempt_number' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement(WebhookAttemptStatus::cases()),
            'error_message' => $this->faker->optional()->sentence(),
            'dispatched_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'responded_at' => $this->faker->optional()->dateTimeBetween('-1 day', 'now'),
            'next_retry_at' => $this->faker->optional()->dateTimeBetween('now', '+1 hour'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WebhookAttemptStatus::PENDING,
            'response_code' => null,
            'response_body' => null,
            'responded_at' => null,
        ]);
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WebhookAttemptStatus::SUCCESS,
            'response_code' => 200,
            'response_body' => 'OK',
            'responded_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'error_message' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WebhookAttemptStatus::FAILED,
            'response_code' => $this->faker->randomElement([400, 404, 500]),
            'error_message' => $this->faker->sentence(),
            'responded_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }
}