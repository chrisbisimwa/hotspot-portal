<?php

namespace App\Providers;

use App\Domain\Billing\Events\PaymentSucceeded;
use App\Domain\Hotspot\Events\HotspotUserProvisioned;
use App\Domain\Hotspot\Events\OrderCompleted;
use App\Domain\Hotspot\Listeners\OnHotspotUserProvisionedSendCredentials;
use App\Domain\Hotspot\Listeners\OnOrderCompletedSendSummary;
use App\Domain\Hotspot\Listeners\OnPaymentSucceededProvisionOrder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
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
    }
}
