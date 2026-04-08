<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DeviceWebhookController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockTransactionController;
use App\Http\Middleware\VerifyDeviceSignature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
|
| Prefix: /api/v1 (configured in bootstrap/app.php)
|
*/

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        $db = 'ok';
    } catch (Exception) {
        $db = 'unavailable';
    }

    $status = $db === 'ok' ? 'ok' : 'degraded';
    $code = $db === 'ok' ? 200 : 503;

    return response()->json(['status' => $status, 'database' => $db], $code);
});

// Auth
Route::prefix('auth')->middleware('throttle:30,1')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Protected routes
Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('categories', CategoryController::class);
    Route::get('products/low-stock', [ProductController::class, 'lowStock']);
    Route::apiResource('products', ProductController::class);

    // Stock transactions
    Route::get('products/{product}/transactions', [StockTransactionController::class, 'index']);
    Route::post('stock-transactions', [StockTransactionController::class, 'store']);

    // Audit logs — Admin only (enforced in ListAuditLogRequest::authorize)
    Route::get('audit-logs', [AuditLogController::class, 'index']);
});

// Device webhook — HMAC-SHA256 verified, no Sanctum auth
Route::post('device/webhook', DeviceWebhookController::class)
    ->middleware(VerifyDeviceSignature::class);
