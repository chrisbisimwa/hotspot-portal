<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\AdminMetricsController;
use App\Http\Controllers\Api\V1\Admin\AdminOrdersController;
use App\Http\Controllers\Api\V1\Admin\AdminPaymentsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Callback\SerdiPayCallbackController;
use App\Http\Controllers\Api\V1\HotspotUserController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\UserProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('api/v1')->name('api.v1.')->group(function () {
    
    // Authentication routes (rate limited for brute force protection)
    Route::middleware(['throttle:api-auth'])->group(function () {
        Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    });
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/auth/token/refresh', [AuthController::class, 'refresh'])->name('auth.refresh'); // TODO placeholder
    });
    
    // Public routes (no auth required)
    Route::get('/user-profiles', [UserProfileController::class, 'index'])->name('user-profiles.index');
    
    // Payment callback (no auth, signature verification inside controller)
    Route::post('/payments/callback/serdipay', [SerdiPayCallbackController::class, 'handle'])->name('payments.callback.serdipay');
    
    // Protected routes (require authentication and role-based rate limiting)
    Route::middleware(['auth:sanctum', App\Http\Middleware\ResolveRoleRateLimiter::class])->group(function () {
        
        // Current user
        Route::get('/me', [MeController::class, 'show'])->name('me.show');
        
        // Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        
        // Payments
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{order}/initiate', [PaymentController::class, 'initiate'])->name('payments.initiate');
        
        // Hotspot Users
        Route::get('/hotspot-users', [HotspotUserController::class, 'index'])->name('hotspot-users.index');
        Route::get('/hotspot-users/{hotspotUser}', [HotspotUserController::class, 'show'])->name('hotspot-users.show');
        Route::get('/hotspot-users/{hotspotUser}/sessions', [HotspotUserController::class, 'sessions'])->name('hotspot-users.sessions');
        
        // Sessions (user's sessions on their hotspot users)
        Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
        
        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
        
        // Admin routes (requires admin role)
        Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/metrics', [AdminMetricsController::class, 'index'])->name('metrics.index');
            Route::get('/orders', [AdminOrdersController::class, 'index'])->name('orders.index');
            Route::get('/payments', [AdminPaymentsController::class, 'index'])->name('payments.index');
        });
    });
});