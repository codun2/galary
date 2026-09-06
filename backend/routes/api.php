<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        $db = 'connected';
    } catch (\Throwable $e) {
        $db = 'error';
    }

    return response()->json(['message' => 'AutoGallery API up', 'db' => $db]);
});

// ---- الموقع العام (بلا تسجيل) ----
Route::get('/site/settings', [SiteController::class, 'settings']);
Route::get('/site/facets', [SiteController::class, 'facets']);
Route::get('/site/vehicles', [SiteController::class, 'vehicles']);
Route::get('/site/vehicles/featured', [SiteController::class, 'featured']);
Route::get('/site/vehicles/{vehicle}', [SiteController::class, 'show']);
Route::post('/site/lead', [SiteController::class, 'lead']);

// ---- دخول المعرض ----
Route::post('/login', [AuthController::class, 'login']);

// ---- لوحة المعرض ----
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/uploads', [UploadController::class, 'store']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/vehicles', [DashboardController::class, 'vehicles']);
    Route::get('/dashboard/vehicles/{vehicle}', [DashboardController::class, 'vehicle']);
    Route::post('/dashboard/vehicles', [DashboardController::class, 'store']);
    Route::put('/dashboard/vehicles/{vehicle}', [DashboardController::class, 'update']);
    Route::delete('/dashboard/vehicles/{vehicle}', [DashboardController::class, 'destroy']);

    Route::get('/dashboard/leads', [DashboardController::class, 'leads']);
    Route::post('/dashboard/leads/{lead}/status', [DashboardController::class, 'leadStatus']);

    Route::get('/dashboard/settings', [DashboardController::class, 'getSettings']);
    Route::put('/dashboard/settings', [DashboardController::class, 'saveSettings']);

    Route::get('/dashboard/catalog', [DashboardController::class, 'catalog']);
    Route::post('/dashboard/makes', [DashboardController::class, 'storeMake']);
    Route::delete('/dashboard/makes/{make}', [DashboardController::class, 'deleteMake']);
    Route::post('/dashboard/makes/{make}/models', [DashboardController::class, 'storeModel']);
    Route::delete('/dashboard/models/{carModel}', [DashboardController::class, 'deleteModel']);
});
