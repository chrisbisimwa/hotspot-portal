<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Export>
 */
class ExportFactory extends Factory
{
    protected $model = Export::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_key' => $this->faker->randomElement(['orders_summary', 'payments_status_breakdown', 'hotspot_usage', 'user_growth']),
            'format' => $this->faker->randomElement(['csv', 'pdf']),
            'status' => $this->faker->randomElement(['queued', 'processing', 'completed', 'failed']),
            'requested_by' => User::factory(),
            'filters' => [
                'date_from' => $this->faker->date(),
                'date_to' => $this->faker->date(),
            ],
            'total_rows' => $this->faker->optional()->numberBetween(1, 1000),
            'file_path' => $this->faker->optional()->filePath(),
            'error_message' => null,
            'started_at' => $this->faker->optional()->dateTime(),
            'finished_at' => $this->faker->optional()->dateTime(),
            'meta' => $this->faker->optional()->randomElements(['cache_hit' => true, 'truncated' => false]),
        ];
    }

    /**
     * Set the export as completed
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'total_rows' => $this->faker->numberBetween(1, 1000),
            'file_path' => 'exports/' . $this->faker->uuid() . '.csv',
            'started_at' => $this->faker->dateTimeBetween('-1 hour', '-30 minutes'),
            'finished_at' => $this->faker->dateTimeBetween('-30 minutes', 'now'),
        ]);
    }

    /**
     * Set the export as failed
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => $this->faker->sentence(),
            'started_at' => $this->faker->dateTimeBetween('-1 hour', '-30 minutes'),
            'finished_at' => $this->faker->dateTimeBetween('-30 minutes', 'now'),
        ]);
    }
}
