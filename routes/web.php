<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\Admin\OrderTrendsController;
use App\Http\Controllers\Admin\ExportDownloadController;
use App\Http\Controllers\Admin\MonitoringDataController;
use App\Http\Controllers\Admin\LogExportController;
use App\Http\Controllers\Admin\HotspotTicketController;

// Home route - redirect to user dashboard placeholder
Route::get('/', function () {
    // TODO: Redirect to user dashboard when implemented
    return view('welcome'); // Temporary placeholder
})->name('home');
// User dashboard (authenticated users)
Route::get('dashboard', \App\Livewire\User\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// User settings routes
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// Admin routes (protected by auth + role:admin middleware)
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');

        // Orders trends (PLACÉ AVANT les routes paramétrées)
        Route::get('/orders/trends', OrderTrendsController::class)->name('orders.trends');

        // Orders
        Route::get('/orders', \App\Livewire\Admin\Orders\ListOrders::class)->name('orders');
        Route::get('/orders/{order}', \App\Livewire\Admin\Orders\ShowOrder::class)
            ->whereNumber('order')
            ->name('orders.show');
        Route::get('/orders/{order}/edit', \App\Livewire\Admin\Orders\EditOrder::class)
            ->whereNumber('order')
            ->name('orders.edit');

        // Export download
        Route::get('/exports/{export}/download', [ExportDownloadController::class, 'download'])
            ->name('exports.download');
        Route::get('/exports', \App\Livewire\Admin\Exports\ExportsList::class)->name('exports.index');

        // Reports
        Route::get('/reports', \App\Livewire\Admin\Reports\ReportsIndex::class)->name('reports.index');
        Route::get('/reports/{reportKey}', \App\Livewire\Admin\Reports\ReportViewer::class)->name('reports.viewer');

        // User Profiles
        Route::get('/user-profiles', \App\Livewire\Admin\UserProfiles\ListUserProfiles::class)->name('user-profiles.index');
        Route::get('/user-profiles/{userProfile}', \App\Livewire\Admin\UserProfiles\ShowUserProfile::class)->name('user-profiles.show');
        Route::get('/user-profiles/{userProfile}/edit', \App\Livewire\Admin\UserProfiles\EditUserProfile::class)->name('user-profiles.edit');

        // Hotspot Users
        Route::get('/hotspot-users', \App\Livewire\Admin\HotspotUsers\ListHotspotUsers::class)->name('hotspot-users.index');
        Route::get('/hotspot-users/{hotspotUser}', \App\Livewire\Admin\HotspotUsers\ShowHotspotUser::class)->name('hotspot-users.show');
        Route::get('/hotspot-users/{hotspotUser}/edit', \App\Livewire\Admin\HotspotUsers\EditHotspotUser::class)->name('hotspot-users.edit');
        Route::get('/{hotspotUser}/ticket.pdf', [HotspotTicketController::class, 'single'])->name('hotspot-users.ticket.pdf');
        Route::get('/tickets.pdf', [HotspotTicketController::class, 'batch'])->name('hotspot-users.tickets.pdf.batch');

        Route::get('/hotspot-sessions', \App\Livewire\Admin\HotspotSessions\ListHotspotSessions::class)->name('hotspot-sessions.index');

        // Payments
        Route::get('/payments', \App\Livewire\Admin\Payments\ListPayments::class)->name('payments.index');
        Route::get('/payments/{payment}', \App\Livewire\Admin\Payments\ShowPayment::class)->name('payments.show');
        Route::get('/payments/{payment}/edit', \App\Livewire\Admin\Payments\EditPayment::class)->name('payments.edit');

        // Notifications
        Route::get('/notifications', \App\Livewire\Admin\Notifications\ListNotifications::class)->name('notifications.index');
        Route::get('/notifications/{notification}', \App\Livewire\Admin\Notifications\ShowNotification::class)->name('notifications.show');
        Route::get('/notifications/{notification}/edit', \App\Livewire\Admin\Notifications\EditNotification::class)->name('notifications.edit');

        // Monitoring (pages + data)
        Route::get('/monitoring/data/interfaces-live', [\App\Http\Controllers\Admin\MonitoringDataController::class, 'interfacesLive']) ->name('monitoring.interfaces.live');
        Route::get('/monitoring', \App\Livewire\Admin\Monitoring\MonitoringCenter::class)->name('monitoring.center');
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/metrics', function (\App\Domain\Monitoring\Services\MetricsService $metricsService) {
                return response()->json([
                    'global' => $metricsService->global(),
                    'system' => $metricsService->system(),
                    'timestamp' => now()->toISOString()
                ]);
            })->name('metrics');
        

            Route::get('/interfaces', function (\App\Domain\Monitoring\Services\MetricsService $metricsService) {
                return response()->json([
                    'interfaces' => $metricsService->interfacesLoad(),
                    'timestamp' => now()->toISOString()
                ]);
            })->name('interfaces');

            Route::get('/data/timeseries', [MonitoringDataController::class, 'timeseries'])->name('timeseries');
            Route::get('/data/interfaces', [MonitoringDataController::class, 'interfaces'])->name('interfaces.data');
        });

        // Logs
        Route::get('/logs', \App\Livewire\Admin\Logs\LogsViewer::class)->name('logs.index');
        Route::get('/logs/export', LogExportController::class)->name('logs.export');

        // Settings
        Route::get('/settings', \App\Livewire\Admin\Settings\SettingsIndex::class)->name('settings.index');
        Route::get('/settings/{group}', \App\Livewire\Admin\Settings\SettingsGroup::class)->name('settings.group');
    });

// Authentication routes
require __DIR__.'/auth.php';

// Health check endpoints (no authentication required)
Route::prefix('health')->name('health.')->group(function () {
    Route::get('/live', [App\Http\Controllers\HealthController::class, 'live'])->name('live');
    Route::get('/ready', [App\Http\Controllers\HealthController::class, 'ready'])->name('ready');
    Route::get('/summary', [App\Http\Controllers\HealthController::class, 'summary'])->name('summary');
});

// Internal metrics endpoint (protected by token)
Route::prefix('internal')->name('internal.')->group(function () {
    Route::get('/metrics', [App\Http\Controllers\Internal\MetricsController::class, 'export'])->name('metrics');
});
