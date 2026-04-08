<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
|
| Prefix: /api/v1 (configured in bootstrap/app.php)
|
*/

Route::get('/health', fn () => response()->json(['status' => 'ok']));

// Auth routes (added in feature/auth)
// Feature routes (added per feature branch)
