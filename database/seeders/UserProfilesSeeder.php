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
            // Profils existants ...
            [
                'name' => '2H',
                'validity_minutes' => 120,
                'price' => 1.50,
                'data_limit_mb' => null,
                'description' => '2 hours internet access package',
                'is_active' => true,
                'mikrotik_profile' => null,
                'rate_limit' => '2M/2M',
                'shared_users' => 1,
            ],
            // ...

            // Nouveaux bundles DATA (quota interne)
            [
                'name' => 'DATA_100MB',
                'validity_minutes' => 1440, // 1 jour (adapter si besoin)
                'price' => 0.80,
                'data_limit_mb' => 100,
                'description' => '100MB data bundle',
                'is_active' => true,
                'mikrotik_profile' => null,
                'rate_limit' => '2M/2M',
                'shared_users' => 1,
            ],
            [
                'name' => 'DATA_1GB',
                'validity_minutes' => 1440,
                'price' => 3.50,
                'data_limit_mb' => 1024,
                'description' => '1GB data bundle',
                'is_active' => true,
                'mikrotik_profile' => null,
                'rate_limit' => '5M/5M',
                'shared_users' => 1,
            ],
            [
                'name' => 'DATA_5GB',
                'validity_minutes' => 7 * 1440, // 1 semaine
                'price' => 12.00,
                'data_limit_mb' => 5120,
                'description' => '5GB data bundle',
                'is_active' => true,
                'mikrotik_profile' => null,
                'rate_limit' => '10M/10M',
                'shared_users' => 1,
            ],
            [
                'name' => 'DATA_10GB',
                'validity_minutes' => 30 * 1440, // 30 jours
                'price' => 20.00,
                'data_limit_mb' => 10240,
                'description' => '10GB data bundle',
                'is_active' => true,
                'mikrotik_profile' => null,
                'rate_limit' => '15M/15M',
                'shared_users' => 1,
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
