<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\EnsureFaceVerified;
use App\Http\Middleware\EnsureProfileComplete;
use App\Http\Middleware\HandleInertiaRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Percayai seluruh proxy untuk mendeteksi HTTPS otomatis di aaPanel
        $middleware->trustProxies(at: '*');

        // Daftarkan alias middleware kustom untuk validasi RBAC & alur wajib login pertama
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'face_verified' => EnsureFaceVerified::class,
            'profile_complete' => EnsureProfileComplete::class,
        ]);

        // Append middleware global untuk kebutuhan response Inertia.js
        $middleware->web(append: [
            HandleInertiaRequests::class,
            \App\Http\Middleware\DetectMobileDeviceMiddleware::class,
            \App\Http\Middleware\LogControllerActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Penanganan pengecualian global tingkat enterprise dapat ditambahkan di sini
    })->create();