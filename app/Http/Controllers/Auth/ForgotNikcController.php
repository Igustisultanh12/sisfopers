<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForgotNikcController extends Controller
{
    /**
     * Tampilkan halaman formulir Lupa NIKC
     */
    public function show()
    {
        $settings = Setting::whereIn('key', [
            'app_name',
            'logo_tni',
            'logo_ad',
            'logo_al',
            'logo_au',
            'ekta_logo_komcad',
            'login_background',
        ])->pluck('value', 'key')->toArray();

        return Inertia::render('Auth/ForgotNikc', [
            'settings' => $settings,
            'result'   => null,
            'input'    => null,
        ]);
    }

    /**
     * Cek data personel berdasarkan Nama, Tanggal Lahir, dan NIK
     */
    public function check(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'dob'       => 'required|date',
            'nik'       => 'required|string',
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi sesuai KTP atau SKEP.',
            'dob.required'       => 'Tanggal lahir wajib diisi.',
            'dob.date'           => 'Format tanggal lahir tidak valid.',
            'nik.required'       => 'NIK wajib diisi.',
        ]);

        $settings = Setting::whereIn('key', [
            'app_name',
            'logo_tni',
            'logo_ad',
            'logo_al',
            'logo_au',
            'ekta_logo_komcad',
            'login_background',
        ])->pluck('value', 'key')->toArray();

        $cleanNik = preg_replace('/[^0-9]/', '', (string)$request->nik);
        if (strlen($cleanNik) !== 16) {
            return Inertia::render('Auth/ForgotNikc', [
                'settings' => $settings,
                'result'   => [
                    'status'  => 'NOT_FOUND',
                    'message' => 'Nomor Induk Kependudukan (NIK) harus terdiri dari 16 digit angka.',
                ],
                'input'    => [
                    'full_name' => $request->full_name,
                    'dob'       => $request->dob,
                    'nik'       => $request->nik,
                ],
            ]);
        }

        try {
            $dobInput = Carbon::parse($request->dob)->toDateString();
        } catch (\Exception $e) {
            $dobInput = null;
        }

        // Cari data di tabel personels berdasarkan NIK
        $personel = Personel::with(['user', 'ekta'])
            ->where('nik', $cleanNik)
            ->first();

        // Jika tidak ditemukan langsung via NIK, coba bersihkan spasi/tanda minus
        if (!$personel) {
            $personel = Personel::with(['user', 'ekta'])
                ->whereRaw("REPLACE(REPLACE(nik, ' ', ''), '-', '') = ?", [$cleanNik])
                ->first();
        }

        // Validasi kesesuaian Nama dan Tanggal Lahir jika personel ditemukan dengan NIK
        if ($personel) {
            $personelDob = null;
            if ($personel->dob) {
                try {
                    $personelDob = Carbon::parse($personel->dob)->toDateString();
                } catch (\Exception $e) {
                    $personelDob = (string)$personel->dob;
                }
            }

            if ($personelDob && $dobInput && $personelDob !== $dobInput) {
                return Inertia::render('Auth/ForgotNikc', [
                    'settings' => $settings,
                    'result'   => [
                        'status'  => 'NOT_FOUND',
                        'message' => 'Data personel dengan kombinasi Nama Lengkap, Tanggal Lahir, dan NIK tersebut tidak cocok. Mohon periksa kembali kesesuaian data Anda.',
                    ],
                    'input'    => [
                        'full_name' => $request->full_name,
                        'dob'       => $request->dob,
                        'nik'       => $request->nik,
                    ],
                ]);
            }

            $normalizeName = function($str) {
                $s = strtolower(trim((string)$str));
                $s = preg_replace('/[^a-z0-9\s]/', '', $s);
                return preg_replace('/\s+/', ' ', $s);
            };

            $dbNameNorm = $normalizeName($personel->full_name);
            $inputNameNorm = $normalizeName($request->full_name);

            $isNameMatch = ($dbNameNorm === $inputNameNorm)
                || str_contains($dbNameNorm, $inputNameNorm)
                || str_contains($inputNameNorm, $dbNameNorm);

            if (!$isNameMatch) {
                return Inertia::render('Auth/ForgotNikc', [
                    'settings' => $settings,
                    'result'   => [
                        'status'  => 'NOT_FOUND',
                        'message' => 'Data personel dengan kombinasi Nama Lengkap, Tanggal Lahir, dan NIK tersebut tidak cocok. Mohon periksa kembali penulisan nama Anda.',
                    ],
                    'input'    => [
                        'full_name' => $request->full_name,
                        'dob'       => $request->dob,
                        'nik'       => $request->nik,
                    ],
                ]);
            }
        } else {
            return Inertia::render('Auth/ForgotNikc', [
                'settings' => $settings,
                'result'   => [
                    'status'  => 'NOT_FOUND',
                    'message' => 'Data personel dengan kombinasi Nama Lengkap, Tanggal Lahir, dan NIK tersebut tidak ditemukan dalam pangkalan data kami.',
                ],
                'input'    => [
                    'full_name' => $request->full_name,
                    'dob'       => $request->dob,
                    'nik'       => $request->nik,
                ],
            ]);
        }

        // Pengecekan apakah personel sudah memiliki akun sistem
        $hasAccount = false;
        if ($personel->user_id) {
            $hasAccount = User::where('id', $personel->user_id)->exists();
        }
        if (!$hasAccount && !empty($personel->nikc)) {
            $hasAccount = User::where('username', trim($personel->nikc))->exists();
        }

        // KONDISI 1: Personel belum memiliki akun sistem (Sensor data & pesan peringatan)
        if (!$hasAccount) {
            return Inertia::render('Auth/ForgotNikc', [
                'settings' => $settings,
                'result'   => [
                    'status'      => 'FOUND_WITHOUT_ACCOUNT',
                    'masked_name' => $this->maskName($personel->full_name),
                    'masked_nikc' => $this->maskNikc($personel->nikc),
                    'message'     => 'Mohon maaf permintaan tidak dapat diproses dikarenakan anda masih belum memiliki akun.',
                ],
                'input'    => [
                    'full_name' => $request->full_name,
                    'dob'       => $request->dob,
                    'nik'       => $request->nik,
                ],
            ]);
        }

        // KONDISI 2: Personel sudah memiliki akun sistem (Tampilkan Data Lengkap)
        $verifyCode = $personel->ekta?->verify_code ?: ('KTA-' . ($personel->angkatan ?: date('Y')) . '-' . substr($personel->nikc ?: 'VERIF', -6));
        $verificationUrl = url('/verifikasi/ekta/' . $verifyCode);
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=0&data=' . urlencode($verificationUrl);

        $photoUrl = $this->resolvePhotoBase64($personel->photo_profile);

        return Inertia::render('Auth/ForgotNikc', [
            'settings' => $settings,
            'result'   => [
                'status'           => 'FOUND_WITH_ACCOUNT',
                'full_name'        => $personel->full_name,
                'nikc'             => $personel->nikc,
                'address'          => $personel->address ?: '-',
                'angkatan'         => $personel->angkatan ?: '-',
                'pangkat'          => Personel::formatShortRank($personel->pangkat),
                'matra'            => $personel->matra ?: 'AD',
                'photo_url'        => $photoUrl,
                'verify_code'      => $verifyCode,
                'verification_url' => $verificationUrl,
                'qr_code_url'      => $qrCodeUrl,
                'nomor_kta'        => $personel->ekta?->nomor_kta,
            ],
            'input'    => [
                'full_name' => $request->full_name,
                'dob'       => $request->dob,
                'nik'       => $request->nik,
            ],
        ]);
    }

    /**
     * Sensor Nama: Huruf pertama tetap tampak, sisanya bintang per kata
     * Contoh: "Nurul Arifin" -> "N****** A*******"
     */
    private function maskName(?string $name): string
    {
        if (!$name) return '-';
        $words = preg_split('/\s+/', trim($name));
        $masked = [];
        foreach ($words as $w) {
            $len = mb_strlen($w);
            if ($len <= 1) {
                $masked[] = $w;
            } else {
                $first = mb_substr($w, 0, 1);
                $starsCount = max(6, $len);
                $masked[] = $first . str_repeat('*', $starsCount);
            }
        }
        return implode(' ', $masked);
    }

    /**
     * Sensor NIKC: Karakter pertama tampak, 5 bintang, karakter ke-7 tampak, sisanya bintang
     * Contoh: "12000018012200216" -> "1*****5***********"
     */
    private function maskNikc(?string $nikc): string
    {
        if (!$nikc) return '-';
        $clean = trim($nikc);
        $len = strlen($clean);
        if ($len < 7) {
            return substr($clean, 0, 1) . str_repeat('*', max(6, $len - 1));
        }
        $first = substr($clean, 0, 1);
        $mid = substr($clean, 6, 1);
        $stars1 = str_repeat('*', 5);
        $stars2 = str_repeat('*', max(6, $len - 7));
        return $first . $stars1 . $mid . $stars2;
    }

    /**
     * Resolusi Pasfoto Personel secara Aman ke Base64 Data URI
     * Foto dibaca langsung dari server disk dan dikonversi ke Base64 Data URI
     * sehingga TIDAK MEMERLUKAN rute berkas terbuka (mencegah akses tanpa izin dari pihak luar).
     */
    private function resolvePhotoBase64(?string $photoProfile): ?string
    {
        if (!$photoProfile) {
            return null;
        }

        $rawPhoto = trim($photoProfile);
        if (str_starts_with($rawPhoto, 'http://') || str_starts_with($rawPhoto, 'https://') || str_starts_with($rawPhoto, 'data:')) {
            return $rawPhoto;
        }

        $cleanPath = str_replace('\\', '/', $rawPhoto);
        $cleanPath = ltrim($cleanPath, '/');
        $cleanPath = preg_replace('/^(app\/private\/|app\/public\/|app\/|private\/|storage\/|public\/|\/storage\/|\/admin\/|admin\/)+/', '', $cleanPath);
        $filename = basename($cleanPath);

        $candidates = [
            storage_path('app/private/' . $cleanPath),
            storage_path('app/public/' . $cleanPath),
            storage_path('app/' . $cleanPath),
            storage_path('app/private/personel/photos/' . $filename),
            storage_path('app/public/personel/photos/' . $filename),
            storage_path('app/personel/photos/' . $filename),
            public_path('storage/' . $cleanPath),
            public_path('storage/personel/photos/' . $filename),
            '/www/wwwroot/sisfoperskc.my.id/storage/app/private/personel/photos/' . $filename,
            '/www/wwwroot/sisfoperskc.my.id/storage/app/public/personel/photos/' . $filename,
            '/www/wwwroot/sisfoperskc.my.id/storage/app/private/' . $cleanPath,
            '/www/wwwroot/sisfoperskc.my.id/storage/app/public/' . $cleanPath,
            '/www/wwwroot/sisfoperskc.my.id/public/storage/' . $cleanPath,
            '/www/wwwroot/sisfopers.site/storage/app/private/' . $cleanPath,
            '/www/wwwroot/sisfopers.site/storage/app/public/' . $cleanPath,
            '/www/wwwroot/sisfopers.site/storage/app/private/personel/photos/' . $filename,
            '/www/wwwroot/sisfopers.site/storage/app/public/personel/photos/' . $filename,
            '/www/wwwroot/sisfopers.site/public/storage/' . $cleanPath,
        ];

        foreach (['private', 'public', 'local'] as $diskName) {
            try {
                if (\Illuminate\Support\Facades\Storage::disk($diskName)->exists($cleanPath)) {
                    $candidates[] = \Illuminate\Support\Facades\Storage::disk($diskName)->path($cleanPath);
                }
                if (\Illuminate\Support\Facades\Storage::disk($diskName)->exists('personel/photos/' . $filename)) {
                    $candidates[] = \Illuminate\Support\Facades\Storage::disk($diskName)->path('personel/photos/' . $filename);
                }
            } catch (\Throwable $e) {}
        }

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate) && is_readable($candidate)) {
                $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png'  => 'image/png',
                    'webp' => 'image/webp',
                    'gif'  => 'image/gif',
                    'svg'  => 'image/svg+xml',
                ];
                $mime = $mimeTypes[$ext] ?? (@mime_content_type($candidate) ?: 'image/jpeg');
                $fileContent = @file_get_contents($candidate);
                if ($fileContent !== false) {
                    return 'data:' . $mime . ';base64,' . base64_encode($fileContent);
                }
            }
        }

        return null;
    }
}
