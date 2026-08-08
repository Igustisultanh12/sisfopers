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

// Rute Pengecekan NIKC & Pengajuan SKEP Publik (Digunakan Saat Form Registrasi)
Route::match(['get', 'post'], '/skep/check', [SkepPublicController::class, 'checkNikc'])->name('skep.check');
Route::match(['get', 'post'], '/skep/request', [SkepPublicController::class, 'submitRequest'])->name('skep.request');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/reset-password-otp', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'create'])->name('password.reset-otp');
    Route::post('/reset-password-otp', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'store'])->name('password.update-otp');
});

// Rute Global Pengguna Terautentikasi (Auth Group)
// Rute Tampilan Under Maintenance (Bisa diakses publik/guest agar register juga bisa dialihkan ke sini)
Route::get('/maintenance', function () {
    if (auth()->check() && !auth()->user()->hasRole('personel')) {
        return redirect()->route('admin.dashboard');
    }
    
    $isMaintenance = \App\Models\Setting::where('key', 'under_maintenance')->value('value') === '1';
    if (!$isMaintenance) {
        return redirect()->route('login');
    }

    return Inertia::render('Maintenance', [
        'settings' => \App\Models\Setting::all()->pluck('value', 'key')
    ]);
})->name('maintenance');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Manajemen Profil & Pengaturan Akun (dengan Alias Rute Ziggy Lengkap)
    Route::get('/account/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['post', 'put', 'patch'], '/account/settings', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/account/settings/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/account/settings/password-update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/account/settings/mfa/toggle', [ProfileController::class, 'toggleMfa'])->name('profile.toggle-mfa');
    Route::post('/account/settings/mfa', [ProfileController::class, 'toggleMfa'])->name('profile.mfa');
    Route::post('/account/settings/mfa/verify', [ProfileController::class, 'verifyMfa'])->name('profile.verify-mfa');
    Route::post('/account/settings/mfa/verify-code', [ProfileController::class, 'verifyMfa'])->name('profile.mfa.verify');

    // Modul Notifikasi System Global & Dropdown Header
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::match(['get', 'post'], '/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/readAll', [NotificationController::class, 'readAll'])->name('notifications.readAll');

    // Rute Unduhan & Display Berkas Privat Aman via Query String (Memotong Intersepsi Static Regex Nginx aaPanel)
    Route::get('/documents/private-stream', function (\Illuminate\Http\Request $request) {
        $path = $request->query('path', '');
        if (!$path) {
            $path = $request->query('file', '');
        }
        if (!$path) {
            abort(404);
        }

        $cleanPath = ltrim($path, '/');
        $cleanPath = preg_replace('/^(app\/private\/|app\/public\/|app\/|private\/|storage\/|public\/)+/', '', $cleanPath);
        $filename = basename($cleanPath);

        $candidates = [
            storage_path('app/private/' . $cleanPath),
            storage_path('app/public/' . $cleanPath),
            storage_path('app/' . $cleanPath),
            storage_path('app/private/personel/photos/' . $filename),
            storage_path('app/public/personel/photos/' . $filename),
            storage_path('app/personel/photos/' . $filename),
            storage_path('app/private/personel/documents/' . $filename),
            storage_path('app/public/personel/documents/' . $filename),
            storage_path('app/personel/documents/' . $filename),
            public_path('storage/' . $cleanPath),
            public_path('storage/personel/photos/' . $filename),
            public_path('storage/personel/documents/' . $filename),
            '/www/wwwroot/sisfopers.site/storage/app/private/' . $cleanPath,
            '/www/wwwroot/sisfopers.site/storage/app/public/' . $cleanPath,
            '/www/wwwroot/sisfopers.site/storage/app/' . $cleanPath,
            '/www/wwwroot/sisfopers.site/storage/app/private/personel/photos/' . $filename,
            '/www/wwwroot/sisfopers.site/storage/app/public/personel/photos/' . $filename,
            '/www/wwwroot/sisfopers.site/storage/app/private/personel/documents/' . $filename,
            '/www/wwwroot/sisfopers.site/storage/app/public/personel/documents/' . $filename,
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate) && is_readable($candidate)) {
                $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                    'webp' => 'image/webp', 'gif' => 'image/gif', 'svg' => 'image/svg+xml',
                    'pdf' => 'application/pdf'
                ];
                $mime = $mimeTypes[$ext] ?? (@mime_content_type($candidate) ?: 'image/jpeg');
                return response()->file($candidate, ['Content-Type' => $mime]);
            }
        }

        $isDocument = str_contains($path, 'documents') || str_contains($path, 'ktp') || str_contains($path, 'skep');
        if ($isDocument) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="250" viewBox="0 0 400 250" fill="none"><rect width="400" height="250" rx="12" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="2"/><path d="M160 100H240M160 130H240M160 160H210" stroke="#94A3B8" stroke-width="4" stroke-linecap="round"/><text x="200" y="200" text-anchor="middle" fill="#64748B" font-family="sans-serif" font-size="14" font-weight="bold">Lampiran Berkas (Tidak Ada File)</text></svg>';
            return response($svg, 200)->header('Content-Type', 'image/svg+xml');
        }

        $avatarSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="250" viewBox="0 0 200 250" fill="none"><rect width="200" height="250" fill="#F1F5F9"/><circle cx="100" cy="90" r="45" fill="#94A3B8"/><path d="M30 220C30 170 60 150 100 150C140 150 170 170 170 220V250H30V220Z" fill="#94A3B8"/><text x="100" y="235" text-anchor="middle" fill="#475569" font-family="sans-serif" font-size="12" font-weight="bold">NO PHOTO</text></svg>';
        return response($avatarSvg, 200)->header('Content-Type', 'image/svg+xml');
    })->name('personel.document.stream');

    // Rute Unduhan & Display Berkas Privat Aman (Guaranteed Image Streamer & SVG Fallback)
    Route::get('/documents/private/{path}', function ($path) {
        $cleanPath = ltrim($path, '/');
        $cleanPath = preg_replace('/^(app\/private\/|app\/public\/|app\/|private\/|storage\/|public\/)+/', '', $cleanPath);
        $filename = basename($cleanPath);

        $candidates = [
            storage_path('app/private/' . $cleanPath),
            storage_path('app/public/' . $cleanPath),
            storage_path('app/' . $cleanPath),
            storage_path('app/private/personel/photos/' . $filename),
            storage_path('app/public/personel/photos/' . $filename),
            storage_path('app/personel/photos/' . $filename),
            storage_path('app/private/personel/documents/' . $filename),
            storage_path('app/public/personel/documents/' . $filename),
            storage_path('app/personel/documents/' . $filename),
            public_path('storage/' . $cleanPath),
            public_path('storage/personel/photos/' . $filename),
            public_path('storage/personel/documents/' . $filename),
            '/www/wwwroot/sisfopers.site/storage/app/private/' . $cleanPath,
            '/www/wwwroot/sisfopers.site/storage/app/public/' . $cleanPath,
            '/www/wwwroot/sisfopers.site/storage/app/' . $cleanPath,
            '/www/wwwroot/sisfopers.site/storage/app/private/personel/photos/' . $filename,
            '/www/wwwroot/sisfopers.site/storage/app/public/personel/photos/' . $filename,
            '/www/wwwroot/sisfopers.site/storage/app/private/personel/documents/' . $filename,
            '/www/wwwroot/sisfopers.site/storage/app/public/personel/documents/' . $filename,
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate) && is_readable($candidate)) {
                $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                    'webp' => 'image/webp', 'gif' => 'image/gif', 'svg' => 'image/svg+xml',
                    'pdf' => 'application/pdf'
                ];
                $mime = $mimeTypes[$ext] ?? (@mime_content_type($candidate) ?: 'image/jpeg');
                return response()->file($candidate, ['Content-Type' => $mime]);
            }
        }

        // Fallback jika file fisik belum ada: Hasilkan SVG Placeholder Cantik agar img tidak pecah!
        $isDocument = str_contains($path, 'documents') || str_contains($path, 'ktp') || str_contains($path, 'skep');
        if ($isDocument) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="250" viewBox="0 0 400 250" fill="none"><rect width="400" height="250" rx="12" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="2"/><path d="M160 100H240M160 130H240M160 160H210" stroke="#94A3B8" stroke-width="4" stroke-linecap="round"/><text x="200" y="200" text-anchor="middle" fill="#64748B" font-family="sans-serif" font-size="14" font-weight="bold">Lampiran Berkas (Tidak Ada File)</text></svg>';
            return response($svg, 200)->header('Content-Type', 'image/svg+xml');
        }

        $avatarSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="250" viewBox="0 0 200 250" fill="none"><rect width="200" height="250" fill="#F1F5F9"/><circle cx="100" cy="90" r="45" fill="#94A3B8"/><path d="M30 220C30 170 60 150 100 150C140 150 170 170 170 220V250H30V220Z" fill="#94A3B8"/><text x="100" y="235" text-anchor="middle" fill="#475569" font-family="sans-serif" font-size="12" font-weight="bold">NO PHOTO</text></svg>';
        return response($avatarSvg, 200)->header('Content-Type', 'image/svg+xml');
    })->where('path', '.*')->name('personel.document.download');
});

// Rute Servis Berkas Storage Fallback (Mendukung Seluruh Ekstensi Gambar & Dokumen: JPG, JPEG, PNG, WEBP, GIF, SVG, BMP, HEIC, AVIF, PDF)
Route::get('/storage/{path}', function ($path) {
    $cleanPath = ltrim($path, '/');
    $cleanPath = preg_replace('/^(storage\/|public\/|private\/)+/', '', $cleanPath);
    $filename = basename($cleanPath);
    $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

    // Seluruh variasi ekstensi gambar & dokumen yang didukung
    $imageExtensions = ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.svg', '.bmp', '.heic', '.heif', '.avif', '.tiff', '.pdf'];
    
    $extVariants = [$filename];
    foreach ($imageExtensions as $ext) {
        $extVariants[] = $filenameWithoutExt . $ext;
        $extVariants[] = $filenameWithoutExt . strtoupper($ext);
    }

    $possibleSubpaths = [];
    foreach (array_unique($extVariants) as $fileVar) {
        $possibleSubpaths[] = $fileVar;
        $possibleSubpaths[] = 'personel/photos/' . $fileVar;
        $possibleSubpaths[] = 'personel/documents/' . $fileVar;
        $possibleSubpaths[] = 'personel/pendidikan/' . $fileVar;
        $possibleSubpaths[] = 'personel/pekerjaan/' . $fileVar;
        $possibleSubpaths[] = 'personel/asn_sks/' . $fileVar;
        $possibleSubpaths[] = 'personel/skep_requests/' . $fileVar;
    }

    foreach (['public', 'local', 'private'] as $diskName) {
        foreach ($possibleSubpaths as $subPath) {
            if (Storage::disk($diskName)->exists($subPath)) {
                $fullPath = Storage::disk($diskName)->path($subPath);
                if (file_exists($fullPath) && is_readable($fullPath)) {
                    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                    $mimeTypes = [
                        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                        'webp' => 'image/webp', 'gif' => 'image/gif', 'svg' => 'image/svg+xml',
                        'bmp' => 'image/bmp', 'heic' => 'image/heic', 'heif' => 'image/heif',
                        'avif' => 'image/avif', 'pdf' => 'application/pdf'
                    ];
                    $mime = $mimeTypes[$ext] ?? (mime_content_type($fullPath) ?: 'image/jpeg');
                    return response()->file($fullPath, ['Content-Type' => $mime]);
                }
            }
        }
    }

    abort(404, 'File gambar tidak ditemukan.');
})->where('path', '.*')->name('storage.fallback');

/*
|--------------------------------------------------------------------------
| 1. Role: Admin System Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,kordinator_matra,kordinator_angkatan'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard & Statistik Inti
    Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('dashboard');

    // Konfirmasi & Verifikasi Pendaftaran Member Baru
    Route::get('/verifikasi', [VerificationController::class, 'index'])->name('verification.index');
    Route::get('/verifikasi/{uuid}', [VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verifikasi/{uuid}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/verifikasi/{uuid}/upload-document', [VerificationController::class, 'uploadDocument'])->name('verification.upload-document');

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
        Route::get('/pengkinian-data', [\App\Http\Controllers\Personel\PengkinianDataController::class, 'adminIndex'])->name('pengkinian-data.index');
        Route::get('/verifikasi-pengkinian/search-personel', [\App\Http\Controllers\Personel\PengkinianDataController::class, 'searchPersonel'])->name('pengkinian-data.search-personel');
        Route::post('/verifikasi-pengkinian/store', [\App\Http\Controllers\Personel\PengkinianDataController::class, 'adminStore'])->name('pengkinian-data.store');
        Route::post('/verifikasi-pengkinian/{id}/verify', [\App\Http\Controllers\Personel\PengkinianDataController::class, 'verify'])->name('pengkinian-data.verify');
        Route::post('/verifikasi-pengkinian/{id}/reject', [\App\Http\Controllers\Personel\PengkinianDataController::class, 'reject'])->name('pengkinian-data.reject');

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
    Route::get('/laporan/wilayah/excel', [ReportController::class, 'regionExcel'])->name('report.region.excel');
    Route::get('/laporan/wilayah/pdf', [ReportController::class, 'regionPdf'])->name('report.region.pdf');
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

    // Manajemen Akun Pejabat Utama (PJU)
    Route::get('/pju-management', [\App\Http\Controllers\Admin\PjuManagementController::class, 'index'])->name('pju.index');
    Route::post('/pju-management', [\App\Http\Controllers\Admin\PjuManagementController::class, 'store'])->name('pju.store');
    Route::put('/pju-management/{id}', [\App\Http\Controllers\Admin\PjuManagementController::class, 'update'])->name('pju.update');
    Route::delete('/pju-management/{id}', [\App\Http\Controllers\Admin\PjuManagementController::class, 'destroy'])->name('pju.destroy');
    Route::get('/pju-management/{id}/print-account', [\App\Http\Controllers\Admin\PjuManagementController::class, 'printAccountPdf'])->name('pju.print-account');

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

    // Modul Open Tiket Pengaduan & Verifikasi (Admin & Koordinator)
    Route::get('/tickets', [\App\Http\Controllers\Admin\TicketAdminController::class, 'index'])->name('tickets.index');
    Route::post('/tickets/{id}/status', [\App\Http\Controllers\Admin\TicketAdminController::class, 'updateStatus'])->name('tickets.update-status');
    Route::delete('/tickets/{id}', [\App\Http\Controllers\Admin\TicketAdminController::class, 'destroy'])->name('tickets.destroy');
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
Route::middleware(['auth', 'role:personel,admin,kordinator_angkatan,kordinator_matra', 'under_maintenance'])->prefix('personel')->name('personel.')->group(function () {
    
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
        Route::get('/pengkinian-data', [\App\Http\Controllers\Personel\PengkinianDataController::class, 'index'])->name('pengkinian-data.index');
        Route::post('/pengkinian-data', [\App\Http\Controllers\Personel\PengkinianDataController::class, 'store'])->name('pengkinian-data.store');

        // Modul Riwayat Pekerjaan (ASN / Non-ASN) Terverifikasi OTP WA & wilayah.id
        Route::get('/pekerjaan', [JobHistoryController::class, 'index'])->name('job.index');
        Route::post('/pekerjaan/otp-request', [JobHistoryController::class, 'requestOtp'])->name('job.otp-request');
        Route::post('/pekerjaan/otp-verify', [JobHistoryController::class, 'verifyOtp'])->name('job.otp-verify');
        Route::post('/pekerjaan', [JobHistoryController::class, 'store'])->name('job.store');
        Route::post('/pekerjaan/phk', [JobHistoryController::class, 'phk'])->name('job.phk');

        // Modul Open Tiket Pengaduan Personel (Ganti Foto, Ubah Data, Cetak KTA)
        Route::get('/tickets', [\App\Http\Controllers\Personel\TicketController::class, 'index'])->name('tickets.index');
        Route::post('/tickets', [\App\Http\Controllers\Personel\TicketController::class, 'store'])->name('tickets.store');

        // Pemutakhiran Data Komando Kewilayahan Personel (1x pengisian)
        Route::post('/personel/update-kewilayahan', [\App\Http\Controllers\Personel\KewilayahanController::class, 'updateKewilayahan'])->name('personel.kewilayahan.update');
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
    
        Route::middleware('role:kordinator_matra,kordinator_angkatan')->group(function () {
        Route::get('/broadcast', [BroadcastKoordinatorController::class, 'index'])->name('broadcast.index');
        Route::get('/broadcast/create', [BroadcastKoordinatorController::class, 'create'])->name('broadcast.create');
        Route::post('/broadcast', [BroadcastKoordinatorController::class, 'store'])->name('broadcast.store');
    });
});

/*
|--------------------------------------------------------------------------
| 5. Role: Pejabat Utama (PJU) Routes (Ka Bacadnas, Ses Bacadnas, Kapus Komcad, Pembina Matra, Pembina Kodam/Kodaeral/Kodau/Kodim/Lanal/Lanud)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ka_bacadnas,ses_bacadnas,kapus_komcad,pembina_matra,pembina_kodam,pembina_kodaeral,pembina_kodau,pembina_kodim,pembina_lanal,pembina_lanud,admin'])->prefix('pju')->name('pju.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Pju\DashboardPjuController::class, 'index'])->name('dashboard');
    Route::get('/personel', [\App\Http\Controllers\Pju\DashboardPjuController::class, 'personelIndex'])->name('personel.index');
    Route::get('/broadcast', [\App\Http\Controllers\Pju\DashboardPjuController::class, 'broadcastIndex'])->name('broadcast.index');
    Route::get('/broadcast/create', [\App\Http\Controllers\Pju\DashboardPjuController::class, 'broadcastCreate'])->name('broadcast.create');
    Route::post('/broadcast', [\App\Http\Controllers\Pju\DashboardPjuController::class, 'broadcastStore'])->name('broadcast.store');
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
