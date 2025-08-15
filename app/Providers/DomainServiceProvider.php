<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\Services\SerdiPayGateway;
use App\Domain\Hotspot\Contracts\MikrotikApiInterface;
use App\Domain\Hotspot\Services\MikrotikApiService;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Mikrotik API interface to service implementation
        $this->app->bind(MikrotikApiInterface::class, MikrotikApiService::class);
        
        // Bind Payment Gateway interface to SerdiPay implementation
        $this->app->bind(PaymentGatewayInterface::class, SerdiPayGateway::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // TODO: Register event listeners for payment events (future step)
        // TODO: Register queue jobs for hotspot provisioning (future step)
    }
}