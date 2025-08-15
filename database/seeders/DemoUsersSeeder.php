<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only run in local environment
        if (app()->environment('production')) {
            return;
        }

        $userRole = Role::where('name', 'user')->first();

        // Generate 5 demo users
        User::factory(5)->create()->each(function (User $user) use ($userRole) {
            // Assign user role if available
            if ($userRole) {
                $user->assignRole('user');
            }
        });

        // TODO: Generate demo orders in later steps when order processing is implemented
    }
}
