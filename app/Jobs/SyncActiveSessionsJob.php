<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Hotspot\Contracts\MikrotikApiInterface;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncActiveSessionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct()
    {
        //
    }

    public function handle(MikrotikApiInterface $mikrotikApi): void
    {
        $startTime = microtime(true);
        
        Log::info('SyncActiveSessionsJob: Starting sync process');

        try {
            // Get active sessions from Mikrotik
            $activeSessions = $mikrotikApi->getActiveSessions();
            
            Log::info('SyncActiveSessionsJob: Retrieved active sessions from Mikrotik', [
                'count' => count($activeSessions)
            ]);

            $sessionsProcessed = 0;
            $sessionsCreated = 0;
            $sessionsUpdated = 0;

            // Process each active session
            foreach ($activeSessions as $sessionData) {
                $username = $sessionData['user'] ?? null;
                $sessionId = $sessionData['.id'] ?? null;
                
                if (!$username || !$sessionId) {
                    continue;
                }

                // Find the hotspot user
                $hotspotUser = HotspotUser::where('username', $username)->first();
                if (!$hotspotUser) {
                    Log::warning('SyncActiveSessionsJob: Hotspot user not found', [
                        'username' => $username
                    ]);
                    continue;
                }

                // Find existing session or create new one
                $session = HotspotSession::where([
                    'hotspot_user_id' => $hotspotUser->id,
                    'mikrotik_session_id' => $sessionId,
                ])->first();

                if ($session) {
                    // Update existing session - keep it active by ensuring stop_time is null
                    $session->update([
                        'stop_time' => null, // Ensure it's active
                        'ip_address' => $sessionData['address'] ?? null,
                        'mac_address' => $sessionData['mac-address'] ?? null,
                        'interface' => $sessionData['interface'] ?? null,
                        'upload_mb' => isset($sessionData['bytes-out']) ? intval($sessionData['bytes-out'] / 1048576) : 0,
                        'download_mb' => isset($sessionData['bytes-in']) ? intval($sessionData['bytes-in'] / 1048576) : 0,
                    ]);
                    $sessionsUpdated++;
                } else {
                    // Create new session
                    $session = HotspotSession::create([
                        'hotspot_user_id' => $hotspotUser->id,
                        'mikrotik_session_id' => $sessionId,
                        'start_time' => now(), // TODO: Use actual start time from Mikrotik
                        'stop_time' => null, // Active session
                        'ip_address' => $sessionData['address'] ?? null,
                        'mac_address' => $sessionData['mac-address'] ?? null,
                        'interface' => $sessionData['interface'] ?? null,
                        'upload_mb' => isset($sessionData['bytes-out']) ? intval($sessionData['bytes-out'] / 1048576) : 0,
                        'download_mb' => isset($sessionData['bytes-in']) ? intval($sessionData['bytes-in'] / 1048576) : 0,
                    ]);
                    $sessionsCreated++;
                }
                
                $sessionsProcessed++;
            }

            // Close sessions that are no longer active in Mikrotik
            $activeSessionIds = collect($activeSessions)->map(function ($session) {
                return $session['.id'] ?? null;
            })->filter()->values()->toArray();
            
            // Only close sessions if we have processed some active sessions
            if (!empty($activeSessionIds)) {
                $sessionsToClose = HotspotSession::whereNull('stop_time')
                    ->whereNotIn('mikrotik_session_id', $activeSessionIds)
                    ->get();
                    
                $closedSessions = $sessionsToClose->each(function ($session) {
                    $sessionDuration = $session->start_time ? now()->diffInSeconds($session->start_time) : 3600;
                    $session->update([
                        'stop_time' => now(),
                        'session_time' => $sessionDuration
                    ]);
                })->count();
            } else {
                // If no active sessions from Mikrotik, close all open sessions
                $openSessions = HotspotSession::whereNull('stop_time')->get();
                $closedSessions = $openSessions->each(function ($session) {
                    $sessionDuration = $session->start_time ? now()->diffInSeconds($session->start_time) : 3600;
                    $session->update([
                        'stop_time' => now(),
                        'session_time' => $sessionDuration
                    ]);
                })->count();
            }

            $executionTime = microtime(true) - $startTime;
            Log::info('SyncActiveSessionsJob: Sync completed successfully', [
                'execution_time_seconds' => round($executionTime, 3),
                'sessions_processed' => $sessionsProcessed,
                'sessions_created' => $sessionsCreated,
                'sessions_updated' => $sessionsUpdated,
                'sessions_closed' => $closedSessions
            ]);

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            Log::error('SyncActiveSessionsJob: Sync failed', [
                'error' => $e->getMessage(),
                'execution_time_seconds' => round($executionTime, 3)
            ]);
            
            throw $e;
        }
    }

    public function tags(): array
    {
        return ['mikrotik', 'sync', 'sessions'];
    }
}