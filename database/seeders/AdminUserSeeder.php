<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user if doesn't exist (idempotent)
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.test'],
            [
                'name' => 'Super Admin',
                'phone' => '+00000000000',
                'password' => Hash::make('password'),
                'user_type' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role if not already assigned
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && ! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
