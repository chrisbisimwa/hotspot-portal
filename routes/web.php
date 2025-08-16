<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

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
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin dashboard
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');

    // Monitoring endpoints
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
    });

    // TODO: Add other admin routes here
    // Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
    // Route::get('/users', [HotspotUserController::class, 'index'])->name('users.index');
    // Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    // Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    // Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    // Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    // Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    // Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    // Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
});

// Authentication routes
require __DIR__.'/auth.php';
