<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\HotspotUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HotspotSession>
 */
class HotspotSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hotspot_user_id' => HotspotUser::factory(),
            'start_time' => fake()->dateTimeBetween('-1 hour', 'now'),
            'stop_time' => null, // Active session by default
            'session_time' => null,
            'upload_mb' => fake()->numberBetween(0, 100),
            'download_mb' => fake()->numberBetween(0, 500),
            'ip_address' => fake()->localIpv4(),
            'mac_address' => fake()->macAddress(),
            'interface' => fake()->randomElement(['wlan1', 'wlan2', 'ether1']),
            'mikrotik_session_id' => 'session_' . fake()->unique()->numberBetween(1000, 9999),
        ];
    }

    /**
     * Indicate that the session is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'stop_time' => fake()->dateTimeBetween($attributes['start_time'] ?? '-30 minutes', 'now'),
            'session_time' => fake()->numberBetween(300, 3600), // 5 minutes to 1 hour
        ]);
    }
}