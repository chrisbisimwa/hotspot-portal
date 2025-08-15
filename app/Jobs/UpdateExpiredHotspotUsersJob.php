<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\HotspotUserStatus;
use App\Models\HotspotUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateExpiredHotspotUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $startTime = microtime(true);
        
        Log::info('UpdateExpiredHotspotUsersJob: Starting expiration check');

        try {
            // Find hotspot users that are expired but not marked as expired
            $expiredUsers = HotspotUser::where('expired_at', '<', now())
                ->where('status', '!=', HotspotUserStatus::EXPIRED->value)
                ->get();

            Log::info('UpdateExpiredHotspotUsersJob: Found expired users', [
                'count' => $expiredUsers->count()
            ]);

            $usersUpdated = 0;

            foreach ($expiredUsers as $user) {
                $oldStatus = $user->status;
                $user->update(['status' => HotspotUserStatus::EXPIRED->value]);
                
                Log::info('UpdateExpiredHotspotUsersJob: User status updated', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'old_status' => $oldStatus,
                    'new_status' => HotspotUserStatus::EXPIRED->value,
                    'expired_at' => $user->expired_at?->toISOString()
                ]);
                
                $usersUpdated++;
            }

            $executionTime = microtime(true) - $startTime;
            Log::info('UpdateExpiredHotspotUsersJob: Expiration check completed', [
                'execution_time_seconds' => round($executionTime, 3),
                'users_updated' => $usersUpdated
            ]);

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            Log::error('UpdateExpiredHotspotUsersJob: Expiration check failed', [
                'error' => $e->getMessage(),
                'execution_time_seconds' => round($executionTime, 3)
            ]);
            
            throw $e;
        }
    }

    public function tags(): array
    {
        return ['hotspot', 'users', 'expiration'];
    }
}