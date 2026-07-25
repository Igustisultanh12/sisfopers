<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiDashboardController;
use App\Http\Controllers\Api\ApiJobHistoryController;
use App\Http\Controllers\Api\ApiNotificationController;
use App\Http\Controllers\Api\ApiBroadcastController;

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

// Rute API Publik untuk Pengambilan Aset Konfigurasi Cloud saat Build APK
Route::get('/settings/public', function () {
    $logoTni = \App\Models\Setting::where('key', 'logo_tni')->value('value');
    $logoAd = \App\Models\Setting::where('key', 'logo_ad')->value('value');
    $logoAl = \App\Models\Setting::where('key', 'logo_al')->value('value');
    $logoAu = \App\Models\Setting::where('key', 'logo_au')->value('value');
    $loginBackground = \App\Models\Setting::where('key', 'login_background')->value('value');
    
    return response()->json([
        'success' => true,
        'app_name' => \App\Models\Setting::where('key', 'app_name')->value('value') ?? 'Sisfoperskc',
        'logo_tni' => $logoTni ? url($logoTni) : null,
        'logo_ad' => $logoAd ? url($logoAd) : null,
        'logo_al' => $logoAl ? url($logoAl) : null,
        'logo_au' => $logoAu ? url($logoAu) : null,
        'login_background' => $loginBackground ? url($loginBackground) : null,
    ]);
});

// Public API routes
Route::post('/auth/login', [ApiAuthController::class, 'login']);

// Protected API routes (Wajib menyertakan Bearer Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/auth/me', [ApiAuthController::class, 'me']);
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [ApiDashboardController::class, 'index']);

    // Job History & OTP
    Route::get('/job-history', [ApiJobHistoryController::class, 'index']);
    Route::post('/job-history/request-otp', [ApiJobHistoryController::class, 'requestOtp']);
    Route::post('/job-history/verify-otp', [ApiJobHistoryController::class, 'verifyOtp']);
    Route::post('/job-history/store', [ApiJobHistoryController::class, 'store']);

    // Notifications
    Route::get('/notifications', [ApiNotificationController::class, 'index']);
    Route::post('/notifications/read-all', [ApiNotificationController::class, 'readAll']);

    // Broadcasts
    Route::get('/broadcasts', [ApiBroadcastController::class, 'index']);
    Route::post('/broadcasts/{uuid}/respond', [ApiBroadcastController::class, 'respond']);

    // Admin & Coordinator Endpoints
    Route::get('/admin/dashboard-stats', [\App\Http\Controllers\Api\ApiAdminController::class, 'dashboardStats']);
    Route::get('/admin/skep-requests', [\App\Http\Controllers\Api\ApiAdminController::class, 'skepRequests']);
    Route::post('/admin/skep-requests/{id}/verify', [\App\Http\Controllers\Api\ApiAdminController::class, 'verifySkepRequest']);
    Route::get('/admin/personel', [\App\Http\Controllers\Api\ApiAdminController::class, 'personelList']);
    Route::post('/admin/broadcasts/store', [\App\Http\Controllers\Api\ApiAdminController::class, 'storeBroadcast']);

    // New Admin Endpoints
    Route::get('/admin/pending-registrations', [\App\Http\Controllers\Api\ApiAdminController::class, 'getPendingRegistrations']);
    Route::post('/admin/pending-registrations/{uuid}/verify', [\App\Http\Controllers\Api\ApiAdminController::class, 'verifyRegistration']);
    Route::get('/admin/logs', [\App\Http\Controllers\Api\ApiAdminController::class, 'getSystemLogs']);
    Route::get('/admin/settings', [\App\Http\Controllers\Api\ApiAdminController::class, 'getSettings']);
    Route::post('/admin/settings/update', [\App\Http\Controllers\Api\ApiAdminController::class, 'updateSettings']);
    Route::post('/admin/settings/test-wa', [\App\Http\Controllers\Api\ApiAdminController::class, 'testWaConnection']);
    Route::get('/admin/reports', [\App\Http\Controllers\Api\ApiAdminController::class, 'getReportSummaries']);
});