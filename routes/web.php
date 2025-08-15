<?php

use Illuminate\Support\Facades\Route;

// Home route - redirect to user dashboard
Route::get('/', function () {
    // TODO: Redirect to actual user dashboard when implemented
    return view('welcome');
})->name('home');

// TODO: Add authentication routes when Sanctum/Breeze is implemented
// For now, routes are placeholders

// Admin routes group - protected by auth and admin role
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    // TODO: Replace with actual Livewire component when implemented
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    // TODO: Add other admin routes
    // Route::get('/users', [UserController::class, 'index'])->name('users.index');
    // Route::get('/hotspot-users', [HotspotUserController::class, 'index'])->name('hotspot-users.index');
    // Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    // Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    // Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    // Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    // Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    // Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    // Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
});
