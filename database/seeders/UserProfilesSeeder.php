<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class UserProfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'name' => '2H',
                'validity_minutes' => 120,
                'price' => 1.50,
                'data_limit_mb' => null,
                'description' => '2 hours internet access package',
                'is_active' => true,
                'mikrotik_profile' => null,
            ],
            [
                'name' => '1DAY',
                'validity_minutes' => 1440,
                'price' => 3.00,
                'data_limit_mb' => null,
                'description' => '1 day internet access package',
                'is_active' => true,
                'mikrotik_profile' => null,
            ],
            [
                'name' => '1WEEK',
                'validity_minutes' => 10080,
                'price' => 12.00,
                'data_limit_mb' => null,
                'description' => '1 week internet access package',
                'is_active' => true,
                'mikrotik_profile' => null,
            ],
        ];

        foreach ($profiles as $profileData) {
            UserProfile::firstOrCreate(
                ['name' => $profileData['name']],
                $profileData
            );
        }
    }
}
