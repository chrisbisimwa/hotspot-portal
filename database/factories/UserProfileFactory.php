<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $packs = ['2H', '4H', '24H', '3DAY', '1WEEK'];

        return [
            'name' => 'PACK '.fake()->unique()->randomElement($packs),
            'mikrotik_profile' => fake()->randomElement([null, 'basic']),
            'price' => fake()->randomFloat(2, 1, 20),
            'validity_minutes' => fake()->randomElement([120, 240, 1440, 10080]),
            'data_limit_mb' => fake()->randomElement([null, 500, 1000, 2048]),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
