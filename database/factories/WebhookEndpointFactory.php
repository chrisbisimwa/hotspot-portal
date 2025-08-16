<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebhookEndpointFactory extends Factory
{
    protected $model = WebhookEndpoint::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Webhook',
            'url' => $this->faker->url(),
            'secret' => $this->faker->optional()->sha256(),
            'is_active' => $this->faker->boolean(80),
            'event_types' => $this->faker->randomElements([
                'PaymentSucceeded',
                'HotspotUserProvisioned',
                'OrderCompleted',
                'ExportCompleted',
                'IncidentStatusChanged',
            ], $this->faker->numberBetween(1, 3)),
            'failure_count' => $this->faker->numberBetween(0, 5),
            'last_failed_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'last_triggered_at' => $this->faker->optional()->dateTimeBetween('-1 day', 'now'),
            'created_by' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withSecret(): static
    {
        return $this->state(fn (array $attributes) => [
            'secret' => $this->faker->sha256(),
        ]);
    }

    public function withoutSecret(): static
    {
        return $this->state(fn (array $attributes) => [
            'secret' => null,
        ]);
    }
}