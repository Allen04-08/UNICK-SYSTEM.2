<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

RateLimiter::for('api', function (Request $request) {
    return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
});

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [\App\Http\Controllers\AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::post('/auth/login', [\App\Http\Controllers\AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/auth/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware(['auth:sanctum']);

    Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index']);
    Route::get('/products/{product}', [\App\Http\Controllers\ProductController::class, 'show']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', fn (Request $r) => $r->user());

        // Customer routes
        Route::middleware('role:customer|staff|admin')->group(function () {
            Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store']);
            Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index']);
            Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show']);
            Route::post('/feedback', [\App\Http\Controllers\UserFeedbackController::class, 'store']);
        });

        // Staff/Admin routes
        Route::middleware('role:staff|admin')->group(function () {
            Route::apiResource('products', \App\Http\Controllers\ProductController::class)->except(['index','show']);
            Route::apiResource('batches', \App\Http\Controllers\ProductionBatchController::class);
            Route::apiResource('inventory-movements', \App\Http\Controllers\InventoryMovementController::class)->only(['index','store']);
            Route::get('/reports/production', [\App\Http\Controllers\ReportController::class, 'production']);
            Route::get('/reports/inventory', [\App\Http\Controllers\ReportController::class, 'inventory']);
            Route::get('/reports/orders', [\App\Http\Controllers\ReportController::class, 'orders']);
            Route::get('/reports/orders.xlsx', [\App\Http\Controllers\ReportController::class, 'ordersExcel']);
            Route::get('/reports/orders.pdf', [\App\Http\Controllers\ReportController::class, 'ordersPdf']);
            Route::get('/dashboard/metrics', [\App\Http\Controllers\DashboardController::class, 'metrics']);
        });

        // Admin only
        Route::middleware('role:admin')->group(function () {
            Route::delete('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'destroy']);
        });
    });
});