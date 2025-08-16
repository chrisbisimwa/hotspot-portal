<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'slug' => $this->faker->unique()->slug() . '-' . $this->faker->randomNumber(4),
            'status' => $this->faker->randomElement(IncidentStatus::cases()),
            'severity' => $this->faker->randomElement(IncidentSeverity::cases()),
            'started_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'detected_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'detection_source' => $this->faker->randomElement(['alert:payment_failure_rate', 'alert:mikrotik_unreachable', 'manual']),
            'summary' => $this->faker->paragraph(),
            'meta' => [
                'code' => $this->faker->word(),
                'context' => [
                    'test' => $this->faker->word(),
                ],
            ],
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncidentStatus::OPEN,
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncidentStatus::RESOLVED,
            'resolved_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'closed_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => IncidentSeverity::CRITICAL,
        ]);
    }
}