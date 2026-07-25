<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Admin\MasterPersonelController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SkepController;
use App\Http\Controllers\Admin\OtpManualController;
use App\Http\Controllers\Admin\OtpResetPasswordController;
use App\Http\Controllers\SkepPublicController;
use App\Http\Controllers\Komandan\DashboardKomandanController;
use App\Http\Controllers\Personel\DashboardPersonelController;
use App\Http\Controllers\Personel\FaceVerificationController;
use App\Http\Controllers\Personel\SinyalmenController;
use App\Http\Controllers\Personel\BroadcastResponseController;
use App\Http\Controllers\Kordinator\DashboardKordinatorController;
use App\Http\Controllers\Kordinator\BroadcastKoordinatorController;
use App\Http\Controllers\Profile\ProfileController; // Pastikan profile controller diimport
use App\Http\Controllers\NotificationController; // Import Controller Notifikasi Global
use App\Http\Controllers\Education\EducationController;
use App\Http\Controllers\Personel\JobHistoryController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public & Guest Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    // Solusi Infinite Loop: Distribusi user cerdas berbasis status autentikasi dan role
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('komandan')) {
            return redirect()->route('komandan.dashboard');
        } elseif ($user->hasRole('personel')) {
            return redirect()->route('personel.dashboard');
        } elseif ($user->hasRole('kordinator_angkatan') || $user->hasRole('kordinator_matra')) {
            return redirect()->route('kordinator.dashboard');
        }
    }

    return redirect()->route('login');
});

// PORTAL VERIFIKASI DOKUMEN PUBLIK (Bisa di-scan via HP tanpa login)
Route::get('/verify-doc/{verify_code}', [\App\Http\Controllers\Public\DocumentVerificationController::class, 'show'])->name('public.verify-doc');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/reset-password-otp', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'create'])->name('password.reset-otp');
    Route::post('/reset-password-otp', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'store'])->name('password.update-otp');
    
    // Rute Publik Pengecekan & Pengajuan SKEP
    Route::get('/skep/check', [SkepPublicController::class, 'checkNikc'])->middleware('throttle:120,1')->name('skep.check');
    Route::post('/skep/request', [SkepPublicController::class, 'submitRequest'])->middleware('throttle:120,1')->name('skep.request');
});

/*
|--------------------------------------------------------------------------
| Authenticated Shared Routes (Akses Bersama Semua Role)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Hub Manajemen Pusat Akun Global (ROMEI Style Terintegrasi)
    Route::get('/account/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    
    // Menggunakan POST agar engine Laravel dapat membaca payload Upload File / Avatar murni
    Route::post('/account/settings', [ProfileController::class, 'update'])->name('profile.update');
    
    // Rute Pembaruan Kata Sandi & Validasi Verifikasi Token OTP M2FA
    Route::put('/account/settings/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/account/settings/mfa', [ProfileController::class, 'toggleMfa'])->name('profile.mfa');
    Route::post('/account/settings/mfa/verify', [ProfileController::class, 'verifyMfa'])->name('profile.mfa.verify');

    // HUB SISTEM NOTIFIKASI INTERAKTIF GLOBAL (Bisa diklik Admin & Personel)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Rute Unduhan Berkas Ijazah/Dokumen Privat Aman
    Route::get('/documents/private/{path}', function ($path) {
        abort_unless(auth()->check(), 403);
        if (!Storage::disk('private')->exists($path)) {
            abort(404);
        }
        return Storage::disk('private')->response($path);
    })->where('path', '.*')->name('personel.document.download');
});

/*
|--------------------------------------------------------------------------
| 1. Role: Admin System Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard & Statistik Inti
    Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('dashboard');

    // Konfirmasi & Verifikasi Pendaftaran Member Baru
    Route::get('/verifikasi', [VerificationController::class, 'index'])->name('verification.index');
    Route::get('/verifikasi/{uuid}', [VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verifikasi/{uuid}', [VerificationController::class, 'verify'])->name('verification.verify');

    // Master Personel Modul (CRUD Lengkap)
    Route::get('/personel', [MasterPersonelController::class, 'index'])->name('personel.index');
    Route::get('/personel/lookup-skep', [MasterPersonelController::class, 'lookupSkep'])->name('personel.lookup-skep');
    Route::get('/personel/create', [MasterPersonelController::class, 'create'])->name('personel.create');
    Route::post('/personel', [MasterPersonelController::class, 'store'])->name('personel.store');
    Route::get('/personel/{uuid}/edit', [MasterPersonelController::class, 'edit'])->name('personel.edit');
    Route::get('/personel/{uuid}/print-account', [MasterPersonelController::class, 'printAccountPdf'])->name('personel.print-account');
    Route::get('/personel/{uuid}/pendidikan', [EducationController::class, 'adminIndex'])->name('personel.education.index');
    Route::post('/personel/{uuid}/pendidikan', [EducationController::class, 'adminStore'])->name('personel.education.store');
    
    // SOLUSI: Mendukung POST & PUT untuk amandemen file biner multipart/form-data via Inertia Form
    Route::match(['POST', 'PUT'], '/personel/{uuid}', [MasterPersonelController::class, 'update'])->name('personel.update');
    
    Route::delete('/personel/{uuid}', [MasterPersonelController::class, 'destroy'])->name('personel.destroy');
    Route::post('/personel/import', [MasterPersonelController::class, 'importExcel'])->name('personel.import');

    Route::post('/pendidikan/{education}/verify', [EducationController::class, 'verify'])->name('education.verify');
    Route::post('/pendidikan/{education}/reject', [EducationController::class, 'reject'])->name('education.reject');
    Route::match(['POST', 'PUT'], '/pendidikan/{education}', [EducationController::class, 'update'])->name('education.update');
    Route::delete('/pendidikan/{education}', [EducationController::class, 'destroy'])->name('education.destroy');

    // Verifikasi Pendidikan — Halaman khusus admin untuk verifikasi semua riwayat pendidikan
    Route::get('/verifikasi-pendidikan', [EducationController::class, 'adminVerifList'])->name('education.verif-list');

    // Broadcast Kegiatan, Latihan, & Mobilisasi Komponen Cadangan
    Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
    Route::get('/broadcast/create', [BroadcastController::class, 'create'])->name('broadcast.create');
    Route::post('/broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');
    Route::get('/broadcast/{uuid}', [BroadcastController::class, 'show'])->name('broadcast.show');
    Route::get('/broadcast/{uuid}/edit', [BroadcastController::class, 'edit'])->name('broadcast.edit');
    Route::put('/broadcast/{uuid}', [BroadcastController::class, 'update'])->name('broadcast.update');
    Route::delete('/broadcast/{uuid}', [BroadcastController::class, 'destroy'])->name('broadcast.destroy');

    // Monitoring Log Akses, Audit Trail, & Real-time Responses
    Route::get('/monitoring/login', [MonitoringController::class, 'loginLogs'])->name('monitoring.login');
    Route::get('/monitoring/aktivitas', [MonitoringController::class, 'activityLogs'])->name('monitoring.activity');
    Route::get('/monitoring/whatsapp', [MonitoringController::class, 'whatsappLogs'])->name('monitoring.whatsapp');

    // Ekspor Data Laporan (PDF & Excel Engine Terpusat)
    Route::get('/laporan', [ReportController::class, 'index'])->name('report.index');
    Route::get('/laporan/personel/pdf', [ReportController::class, 'personelPdf'])->name('report.personel.pdf');
    Route::get('/laporan/personel/excel', [ReportController::class, 'personelExcel'])->name('report.personel.excel');
    Route::get('/laporan/broadcast/{uuid}/pdf', [ReportController::class, 'broadcastPdf'])->name('report.broadcast.pdf');
    Route::get('/laporan/broadcast/{uuid}/excel', [ReportController::class, 'broadcastExcel'])->name('report.broadcast.excel');

    // Pengaturan Sistem, Local WA Gateway & Google Authenticator MFA
    Route::get('/pengaturan', [SettingController::class, 'index'])->name('setting.index');
    Route::post('/pengaturan', [SettingController::class, 'update'])->name('setting.update');
    Route::post('/pengaturan/wa-test', [SettingController::class, 'testWaConnection'])->name('setting.wa-test');
    Route::post('/pengaturan/wa-test-send', [SettingController::class, 'testSendWa'])->name('setting.wa-test-send');

    // Mail Gateway — Pengaturan SMTP Email Server & Test Mail OTP
    Route::get('/mail-gateway', [\App\Http\Controllers\Admin\MailGatewayController::class, 'index'])->name('mail.index');
    Route::post('/mail-gateway', [\App\Http\Controllers\Admin\MailGatewayController::class, 'update'])->name('mail.update');
    Route::post('/mail-gateway/test', [\App\Http\Controllers\Admin\MailGatewayController::class, 'testSend'])->name('mail.test');

    // Manajemen Database & Pengajuan Berkas SKEP
    Route::get('/skep', [SkepController::class, 'index'])->name('skep.index');
    Route::post('/skep/import', [SkepController::class, 'import'])->name('skep.import');
    Route::delete('/skep/{id}', [SkepController::class, 'destroy'])->name('skep.destroy');
    Route::post('/skep/verify/{id}', [SkepController::class, 'verify'])->name('skep.verify');

    // OTP Manual — Generate & Cetak PDF untuk personel yang belum verifikasi OTP
    Route::get('/otp-manual', [OtpManualController::class, 'index'])->name('otp.index');
    Route::post('/otp-manual/{id}/generate', [OtpManualController::class, 'generate'])->name('otp.generate');
    Route::post('/otp-manual/generate-all', [OtpManualController::class, 'generateAll'])->name('otp.generate-all');
    Route::get('/otp-manual/{id}/pdf', [OtpManualController::class, 'printPdf'])->name('otp.pdf');
    Route::get('/otp-manual/bulk-pdf', [OtpManualController::class, 'printBulkPdf'])->name('otp.bulk-pdf');

    // OTP Reset Password — Generate & Cetak PDF untuk reset password personel aktif
    Route::get('/otp-reset-password', [OtpResetPasswordController::class, 'index'])->name('otp.reset-password.index');
    Route::post('/otp-reset-password/{id}/generate', [OtpResetPasswordController::class, 'generate'])->name('otp.reset-password.generate');
    Route::post('/otp-reset-password/generate-all', [OtpResetPasswordController::class, 'generateAll'])->name('otp.reset-password.generate-all');
    Route::get('/otp-reset-password/{id}/pdf', [OtpResetPasswordController::class, 'printPdf'])->name('otp.reset-password.pdf');
    Route::get('/otp-reset-password/bulk-pdf', [OtpResetPasswordController::class, 'printBulkPdf'])->name('otp.reset-password.bulk-pdf');
});

/*
|--------------------------------------------------------------------------
| 2. Role: Komandan Satuan Routes (Read-Only Rights)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:komandan'])->prefix('komandan')->name('komandan.')->group(function () {
    Route::get('/dashboard', [DashboardKomandanController::class, 'index'])->name('dashboard');
    Route::get('/personel', [DashboardKomandanController::class, 'personelIndex'])->name('personel.index');
    Route::get('/broadcast', [DashboardKomandanController::class, 'broadcastIndex'])->name('broadcast.index');
    Route::get('/broadcast/{uuid}', [DashboardKomandanController::class, 'broadcastShow'])->name('broadcast.show');
    Route::get('/laporan', [DashboardKomandanController::class, 'reportIndex'])->name('report.index');
});

/*
|--------------------------------------------------------------------------
| 3. Role: Personel Komponen Cadangan Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:personel,admin,kordinator_angkatan,kordinator_matra'])->prefix('personel')->name('personel.')->group(function () {
    
    // Gerbang Validasi Alur Pertama (Bebas dari profile_complete untuk mencegah infinite loop)
    Route::get('/face-verification', [FaceVerificationController::class, 'index'])->name('face-verification');
    Route::post('/face-verification', [FaceVerificationController::class, 'store'])->name('face-verification.store');
    Route::post('/face-verification/verify-otp', [FaceVerificationController::class, 'store'])->name('face-verification.verify-otp');
    Route::post('/face-verification/request-otp', [FaceVerificationController::class, 'requestOtp'])->name('face-verification.request-otp');
    Route::post('/face-verification/otp-request', [FaceVerificationController::class, 'requestOtp'])->name('face-verification.otp-request');
    Route::post('/face-verification/otp-manual', [\App\Http\Controllers\Admin\OtpManualController::class, 'verifyManual'])->name('face-verification.otp-manual');
    
    // Gerbang Validasi Alur Kedua (Bebas dari profile_complete karena bertindak sebagai halaman pengisian)
    Route::get('/sinyalmen/lengkap', [SinyalmenController::class, 'create'])->name('sinyalmen.create');
    Route::post('/sinyalmen/lengkap', [SinyalmenController::class, 'store'])->name('sinyalmen.store');

    // Alur Utama Setelah Lolos Seluruh Gerbang Validasi Fisik & Administrasi
    Route::middleware(['face_verified', 'profile_complete'])->group(function () {
        Route::get('/dashboard', [DashboardPersonelController::class, 'index'])->name('dashboard');
        
        // Modul Broadcast Saya & Konfirmasi Kehadiran (Respon)
        Route::get('/broadcast', [BroadcastResponseController::class, 'index'])->name('broadcast.index');
        Route::get('/broadcast/{uuid}', [BroadcastResponseController::class, 'show'])->name('broadcast.show');
        Route::post('/broadcast/{uuid}/respon', [BroadcastResponseController::class, 'respond'])->name('broadcast.respond');
        Route::get('/riwayat', [BroadcastResponseController::class, 'history'])->name('broadcast.history');

        // Modul Riwayat Pendidikan dan Diklat
        Route::get('/pendidikan', [EducationController::class, 'index'])->name('education.index');
        Route::post('/pendidikan', [EducationController::class, 'store'])->name('education.store');
        Route::match(['POST', 'PUT'], '/pendidikan/{education}', [EducationController::class, 'update'])->name('education.update');
        Route::delete('/pendidikan/{education}', [EducationController::class, 'destroy'])->name('education.destroy');

        // Modul Riwayat Pekerjaan (ASN / Non-ASN) Terverifikasi OTP WA & wilayah.id
        Route::get('/pekerjaan', [JobHistoryController::class, 'index'])->name('job.index');
        Route::post('/pekerjaan/otp-request', [JobHistoryController::class, 'requestOtp'])->name('job.otp-request');
        Route::post('/pekerjaan/otp-verify', [JobHistoryController::class, 'verifyOtp'])->name('job.otp-verify');
        Route::post('/pekerjaan', [JobHistoryController::class, 'store'])->name('job.store');
        Route::post('/pekerjaan/phk', [JobHistoryController::class, 'phk'])->name('job.phk');
    });
});

/*
|--------------------------------------------------------------------------
| 4. Role: Koordinator (Angkatan & Matra) Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:kordinator_angkatan,kordinator_matra'])->prefix('kordinator')->name('kordinator.')->group(function () {
    Route::get('/dashboard', [DashboardKordinatorController::class, 'index'])->name('dashboard');
    Route::post('/personel/{uuid}/catatan', [DashboardKordinatorController::class, 'updateNotes'])->name('personel.catatan');
    
    // Broadcast Khusus Koordinator Matra & Angkatan
    Route::middleware('role:kordinator_matra,kordinator_angkatan')->group(function () {
        Route::get('/broadcast', [BroadcastKoordinatorController::class, 'index'])->name('broadcast.index');
        Route::get('/broadcast/create', [BroadcastKoordinatorController::class, 'create'])->name('broadcast.create');
        Route::post('/broadcast', [BroadcastKoordinatorController::class, 'store'])->name('broadcast.store');
    });
});

// API Proxy Wilayah.id (Bypass CORS, SSL & Rate limits with Cache & Safe Fallbacks)
Route::prefix('api/wilayah')->name('wilayah.')->group(function () {
    Route::get('/provinces', function () {
        return \Illuminate\Support\Facades\Cache::remember('wilayah:provinces:v2', 86400 * 7, function () {
            $fallbackProvinces = function () {
                return \App\Models\MasterProvinsi::where('is_active', true)
                    ->orderBy('kode_latsarmil')
                    ->get()
                    ->map(fn ($master) => [
                        'code' => $master->kode_wilayah,
                        'name' => $master->nama,
                        'wilayah_name' => $master->nama_wilayah,
                        'latsarmil_code' => $master->kode_latsarmil,
                    ])
                    ->values()
                    ->toArray();
            };

            try {
                $masterProvinsi = \App\Models\MasterProvinsi::where('is_active', true)
                    ->get()
                    ->keyBy('kode_wilayah');

                if ($masterProvinsi->isEmpty()) {
                    return $fallbackProvinces();
                }

                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                    ->timeout(10)
                    ->get('https://wilayah.id/api/provinces.json');

                if (!$response->successful()) {
                    return $fallbackProvinces();
                }

                $data = $response->json('data') ?? [];

                $mapped = collect($data)->map(function ($item) use ($masterProvinsi) {
                    $kodeWilayah = $item['code'] ?? '';
                    $master = $masterProvinsi->get($kodeWilayah);

                    if (!$master) {
                        return null;
                    }

                    return [
                        'code' => $kodeWilayah,
                        'name' => $master->nama,
                        'wilayah_name' => $item['name'] ?? $master->nama_wilayah,
                        'latsarmil_code' => $master->kode_latsarmil,
                    ];
                })->filter()->values();

                return $mapped->isNotEmpty() ? $mapped->toArray() : $fallbackProvinces();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('API Wilayah Provinces Error: ' . $e->getMessage());
                return $fallbackProvinces();
            }
        });
    })->name('provinces');
    
    Route::get('/regencies/{province_code}', function ($provinceCode) {
        $cleanCode = preg_replace('/[^0-9.]/', '', (string) $provinceCode);
        if (empty($cleanCode)) {
            return response()->json([]);
        }

        return \Illuminate\Support\Facades\Cache::remember("wilayah:regencies:{$cleanCode}", 86400 * 7, function () use ($cleanCode) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                    ->timeout(10)
                    ->get("https://wilayah.id/api/regencies/{$cleanCode}.json");

                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json('data') ?? [];

                return collect($data)->map(function ($item) use ($cleanCode) {
                    return [
                        'code' => $item['code'] ?? '',
                        'name' => $item['name'] ?? '',
                        'province_code' => $cleanCode
                    ];
                })->values()->toArray();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('API Wilayah Regencies Error: ' . $e->getMessage());
                return [];
            }
        });
    })->name('regencies');
    
    Route::get('/districts/{regency_code}', function ($regencyCode) {
        $cleanCode = preg_replace('/[^0-9.]/', '', (string) $regencyCode);
        if (empty($cleanCode)) {
            return response()->json([]);
        }

        return \Illuminate\Support\Facades\Cache::remember("wilayah:districts:{$cleanCode}", 86400 * 7, function () use ($cleanCode) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                    ->timeout(10)
                    ->get("https://wilayah.id/api/districts/{$cleanCode}.json");

                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json('data') ?? [];

                return collect($data)->map(function ($item) use ($cleanCode) {
                    return [
                        'code' => $item['code'] ?? '',
                        'name' => $item['name'] ?? '',
                        'regency_code' => $cleanCode
                    ];
                })->values()->toArray();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('API Wilayah Districts Error: ' . $e->getMessage());
                return [];
            }
        });
    })->name('districts');

    Route::get('/villages/{district_code}', function ($districtCode) {
        $cleanCode = preg_replace('/[^0-9.]/', '', (string) $districtCode);
        if (empty($cleanCode)) {
            return response()->json([]);
        }

        return \Illuminate\Support\Facades\Cache::remember("wilayah:villages:{$cleanCode}", 86400 * 7, function () use ($cleanCode) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                    ->timeout(10)
                    ->get("https://wilayah.id/api/villages/{$cleanCode}.json");

                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json('data') ?? [];

                return collect($data)->map(function ($item) use ($cleanCode) {
                    return [
                        'code' => $item['code'] ?? '',
                        'name' => $item['name'] ?? '',
                        'district_code' => $cleanCode,
                    ];
                })->values()->toArray();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('API Wilayah Villages Error: ' . $e->getMessage());
                return [];
            }
        });
    })->name('villages');
});
