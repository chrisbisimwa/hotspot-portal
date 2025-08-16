<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Reporting\Builders\HotspotUsageReportBuilder;
use App\Domain\Reporting\Builders\OrdersSummaryReportBuilder;
use App\Domain\Reporting\Builders\PaymentsStatusBreakdownReportBuilder;
use App\Domain\Reporting\Builders\UserGrowthReportBuilder;
use App\Domain\Reporting\Services\ReportRegistry;
use Illuminate\Support\ServiceProvider;

class ReportingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the report registry as a singleton
        $this->app->singleton(ReportRegistry::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $registry = $this->app->make(ReportRegistry::class);

        // Register all available report builders
        $registry->register(new OrdersSummaryReportBuilder());
        $registry->register(new PaymentsStatusBreakdownReportBuilder());
        $registry->register(new HotspotUsageReportBuilder());
        $registry->register(new UserGrowthReportBuilder());
    }
}
