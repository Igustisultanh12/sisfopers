<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Otorisasi akses berkas privat yang dilayani rute /documents/*.
 *
 * Streamer bersifat generik (melayani berkas apa pun berdasarkan path), sehingga
 * tanpa cek ini setiap pengguna terautentikasi bisa mengunduh berkas privat
 * (KTP/SKEP/ijazah) milik personel lain bila tahu path-nya.
 *
 * Aturan:
 * - Staff (admin, komandan, kordinator_matra, kordinator_angkatan, akun PJU) boleh
 *   melihat berkas personel (sesuai kebutuhan operasional verifikasi).
 * - Personel biasa hanya boleh mengunduh berkas yang dimilikinya sendiri
 *   (lihat Personel::ownedDocumentPaths()).
 * - Tamu tak terautentikasi tidak akan sampai sini (rute di grup auth:web,pju).
 */
class DocumentAccess
{
    public static function authorize(string $cleanPath, string $filename): void
    {
        $web = Auth::guard('web')->user();
        $pju = Auth::guard('pju')->user();

        // Akun PJU = staff.
        if ($pju) {
            return;
        }

        if ($web && method_exists($web, 'hasRole')) {
            foreach (['admin', 'komandan', 'kordinator_matra', 'kordinator_angkatan'] as $staffRole) {
                if ($web->hasRole($staffRole)) {
                    return;
                }
            }

            // Personel biasa: wajib pemilik berkas.
            if ($web->hasRole('personel')) {
                $personel = $web->personel ?? null;
                $owned = $personel ? $personel->ownedDocumentPaths() : [];

                if (in_array($cleanPath, $owned, true) || in_array($filename, $owned, true)) {
                    return;
                }
                foreach ($owned as $ownedPath) {
                    // Izinkan berkas di dalam folder milik personel (mis. chat_attachments/{uuid}/...).
                    if ($ownedPath !== '' && str_starts_with($cleanPath, rtrim($ownedPath, '/') . '/')) {
                        return;
                    }
                }
                abort(404);
            }
        }

        // Peran lain yang tak dikenal: tolak demi aman.
        abort(404);
    }
}
