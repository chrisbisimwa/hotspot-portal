<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Console\Command;

class DevRebuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:rebuild';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the database with fresh migrations and seeders for development';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('This command cannot be run in production environment.');

            return self::FAILURE;
        }

        $this->info('Rebuilding database...');

        // Fresh migration with seeding
        $this->call('migrate:fresh', ['--seed' => true]);

        // Display summary
        $this->displaySummary();

        $this->info('Database rebuild completed successfully!');

        return self::SUCCESS;
    }

    /**
     * Display a summary of created data.
     */
    private function displaySummary(): void
    {
        $userCount = User::count();
        $profileCount = UserProfile::count();

        $this->table(
            ['Resource', 'Count'],
            [
                ['Users', $userCount],
                ['User Profiles', $profileCount],
            ]
        );

        // Show admin credentials
        $admin = User::where('email', 'admin@demo.test')->first();
        if ($admin) {
            $this->info('Admin credentials:');
            $this->line("Email: {$admin->email}");
            $this->line('Password: password');
        }
    }
}
