<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Shared\Services\NotificationService;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchPendingNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    private int $batchSize;

    public function __construct(?int $batchSize = null)
    {
        $this->batchSize = $batchSize ?? config('notifications.dispatch_batch', 50);
    }

    public function handle(NotificationService $notificationService): void
    {
        $startTime = microtime(true);
        
        Log::info('DispatchPendingNotificationsJob: Starting dispatch process', [
            'batch_size' => $this->batchSize
        ]);

        try {
            // Get queued notifications to send
            $queuedNotifications = Notification::where('status', NotificationStatus::QUEUED->value)
                ->limit($this->batchSize)
                ->get();

            Log::info('DispatchPendingNotificationsJob: Found queued notifications', [
                'count' => $queuedNotifications->count()
            ]);

            $notificationsProcessed = 0;
            $notificationsSent = 0;
            $notificationsFailed = 0;

            foreach ($queuedNotifications as $notification) {
                try {
                    $success = false;
                    
                    // Send notification based on channel
                    if ($notification->channel === 'sms') {
                        $success = $notificationService->sendSms(
                            $notification->to,
                            $notification->message,
                            $notification->meta ?? []
                        );
                    } elseif ($notification->channel === 'email') {
                        $success = $notificationService->sendEmail(
                            $notification->to,
                            $notification->subject ?? 'Notification',
                            $notification->message,
                            $notification->meta ?? []
                        );
                    }

                    // Update notification status
                    if ($success) {
                        $notification->update([
                            'status' => NotificationStatus::SENT->value,
                            'sent_at' => now()
                        ]);
                        $notificationsSent++;
                        
                        Log::info('DispatchPendingNotificationsJob: Notification sent successfully', [
                            'notification_id' => $notification->id,
                            'channel' => $notification->channel,
                            'to' => $notification->to
                        ]);
                    } else {
                        $notification->update([
                            'status' => NotificationStatus::FAILED->value
                        ]);
                        $notificationsFailed++;
                        
                        Log::warning('DispatchPendingNotificationsJob: Notification send failed', [
                            'notification_id' => $notification->id,
                            'channel' => $notification->channel,
                            'to' => $notification->to
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    $notification->update([
                        'status' => NotificationStatus::FAILED->value
                    ]);
                    $notificationsFailed++;
                    
                    Log::error('DispatchPendingNotificationsJob: Notification dispatch error', [
                        'notification_id' => $notification->id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                $notificationsProcessed++;
            }

            $executionTime = microtime(true) - $startTime;
            Log::info('DispatchPendingNotificationsJob: Dispatch completed', [
                'execution_time_seconds' => round($executionTime, 3),
                'notifications_processed' => $notificationsProcessed,
                'notifications_sent' => $notificationsSent,
                'notifications_failed' => $notificationsFailed
            ]);

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            Log::error('DispatchPendingNotificationsJob: Dispatch process failed', [
                'error' => $e->getMessage(),
                'execution_time_seconds' => round($executionTime, 3)
            ]);
            
            throw $e;
        }
    }

    public function tags(): array
    {
        return ['notifications', 'dispatch'];
    }
}