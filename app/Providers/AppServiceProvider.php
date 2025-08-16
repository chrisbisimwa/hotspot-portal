<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Alerting\DTO\AlertMessage;
use App\Domain\Billing\Events\PaymentSucceeded;
use App\Domain\Hotspot\Events\HotspotUserProvisioned;
use App\Domain\Hotspot\Events\OrderCompleted;
use App\Domain\Hotspot\Listeners\OnHotspotUserProvisionedSendCredentials;
use App\Domain\Hotspot\Listeners\OnOrderCompletedSendSummary;
use App\Domain\Hotspot\Listeners\OnPaymentSucceededProvisionOrder;
use App\Domain\Reporting\Events\ExportCompleted;
use App\Domain\Webhooks\Services\WebhookEventDispatcher;
use App\Events\IncidentStatusChanged;
use App\Listeners\OnAlertMessageDispatched;
use App\Listeners\SlowQueryListener;
use App\Services\Feature\FeatureService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register FeatureService as singleton
        $this->app->singleton(FeatureService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register existing event listeners
        Event::listen(
            PaymentSucceeded::class,
            OnPaymentSucceededProvisionOrder::class
        );

        Event::listen(
            HotspotUserProvisioned::class,
            OnHotspotUserProvisionedSendCredentials::class
        );

        Event::listen(
            OrderCompleted::class,
            OnOrderCompletedSendSummary::class
        );

        // Register alerting and incident listeners
        Event::listen(
            AlertMessage::class,
            OnAlertMessageDispatched::class
        );

        // Register webhook event listeners
        Event::listen(
            PaymentSucceeded::class,
            function (PaymentSucceeded $event) {
                app(WebhookEventDispatcher::class)->handle('PaymentSucceeded', [
                    'payment_id' => $event->payment->id,
                    'order_id' => $event->order->id,
                    'amount' => $event->payment->amount,
                    'currency' => $event->payment->currency,
                ]);
            }
        );

        Event::listen(
            HotspotUserProvisioned::class,
            function (HotspotUserProvisioned $event) {
                app(WebhookEventDispatcher::class)->handle('HotspotUserProvisioned', [
                    'hotspot_user_id' => $event->hotspotUser->id,
                    'username' => $event->hotspotUser->username,
                    'profile_name' => $event->hotspotUser->profile->name,
                    'order_id' => $event->order->id,
                ]);
            }
        );

        Event::listen(
            OrderCompleted::class,
            function (OrderCompleted $event) {
                app(WebhookEventDispatcher::class)->handle('OrderCompleted', [
                    'order_id' => $event->order->id,
                    'user_id' => $event->order->user_id,
                    'amount' => $event->order->amount,
                    'status' => $event->order->status->value,
                ]);
            }
        );

        // Check if ExportCompleted event exists
        if (class_exists(ExportCompleted::class)) {
            Event::listen(
                ExportCompleted::class,
                function (ExportCompleted $event) {
                    app(WebhookEventDispatcher::class)->handle('ExportCompleted', [
                        'export_id' => $event->export->id,
                        'report_key' => $event->export->report_key,
                        'format' => $event->export->format,
                        'file_path' => $event->export->file_path,
                    ]);
                }
            );
        }

        Event::listen(
            IncidentStatusChanged::class,
            function (IncidentStatusChanged $event) {
                app(WebhookEventDispatcher::class)->handle(
                    'IncidentStatusChanged',
                    $event->toWebhookPayload()
                );
            }
        );

        // Register slow query listener
        Event::listen(QueryExecuted::class, SlowQueryListener::class);
    }
}
