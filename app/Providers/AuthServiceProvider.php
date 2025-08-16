<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\HotspotUser;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Policies\HotspotUserPolicy;
use App\Policies\MetricsPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        Payment::class => PaymentPolicy::class,
        HotspotUser::class => HotspotUserPolicy::class,
        Notification::class => NotificationPolicy::class,
        \App\Models\Incident::class => \App\Policies\IncidentPolicy::class,
        \App\Models\WebhookEndpoint::class => \App\Policies\WebhookEndpointPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Admin access gate
        Gate::define('admin-access', function ($user) {
            return $user->hasRole('admin');
        });

        // Metrics access gate  
        Gate::define('view-metrics', [MetricsPolicy::class, 'view']);

        // Alerting management gate
        Gate::define('manage-alerting', function ($user) {
            return $user->hasRole('admin');
        });

        // Admin bypass for all policies
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });
    }
}