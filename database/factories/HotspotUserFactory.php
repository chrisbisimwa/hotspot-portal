<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\HotspotUserStatus;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HotspotUser>
 */
class HotspotUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => 'HS'.strtoupper(Str::random(5)),
            'password' => Str::random(8),
            'user_profile_id' => UserProfile::factory(),
            'owner_id' => User::factory(),
            'status' => HotspotUserStatus::ACTIVE->value,
            'validity_minutes' => 120,
            'data_limit_mb' => null,
            'mikrotik_id' => null,
            'expired_at' => null,
            'last_login_at' => null,
        ];
    }
}
