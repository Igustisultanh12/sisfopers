<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Pembaruan aplikasi Android SISFOPERS KC yang diunggah Admin di Pengaturan Sistem.
 *
 * APK disimpan di disk privat (storage/app/private/app-updates) dan metadatanya
 * di tabel settings (kunci `app_update_meta`). Aplikasi memeriksa
 * /api/m/app/update lalu mengunduh dari /api/m/app/update/download, kemudian
 * mencocokkan SHA-256 dan tanda tangan APK sebelum memasang.
 */
class AppRelease
{
    public const PACKAGE = 'id.my.sisfoperskc.app';
    public const DISK = 'local';
    public const DIR = 'app-updates';
    public const KEY = 'app_update_meta';

    /** @return array<string, mixed>|null */
    public static function current(): ?array
    {
        $raw = Setting::where('key', self::KEY)->value('value');
        $meta = $raw ? json_decode((string) $raw, true) : null;
        if (!is_array($meta) || empty($meta['path']) || empty($meta['version'])) {
            return null;
        }
        if (!Storage::disk(self::DISK)->exists($meta['path'])) {
            return null;
        }
        return $meta;
    }

    /** Data publik untuk aplikasi (tanpa lokasi berkas di server). */
    public static function publicPayload(): array
    {
        $m = self::current();
        if (!$m) {
            return ['success' => true, 'available' => false];
        }
        return [
            'success' => true,
            'available' => true,
            'version' => (string) $m['version'],
            'version_code' => (int) ($m['version_code'] ?? 0),
            'notes' => (string) ($m['notes'] ?? ''),
            'size' => (int) ($m['size'] ?? 0),
            'sha256' => (string) ($m['sha256'] ?? ''),
            'file_name' => 'SISFOPERSKC-' . $m['version'] . '.apk',
            'uploaded_at' => (string) ($m['uploaded_at'] ?? ''),
            'download_url' => url('/api/m/app/update/download') . '?v=' . rawurlencode((string) $m['version']),
        ];
    }

    /** Ringkasan untuk halaman Pengaturan Sistem. */
    public static function adminSummary(): ?array
    {
        $m = self::current();
        if (!$m) {
            return null;
        }
        return [
            'version' => $m['version'],
            'version_code' => (int) ($m['version_code'] ?? 0),
            'notes' => (string) ($m['notes'] ?? ''),
            'size' => (int) ($m['size'] ?? 0),
            'sha256' => (string) ($m['sha256'] ?? ''),
            'uploaded_at' => (string) ($m['uploaded_at'] ?? ''),
            'uploaded_by' => (string) ($m['uploaded_by'] ?? ''),
            'notified' => (int) ($m['notified'] ?? 0),
        ];
    }

    /**
     * Simpan APK baru. Mengembalikan pesan galat (string) atau metadata (array).
     *
     * @return array<string, mixed>|string
     */
    public static function store(UploadedFile $file, string $notes, string $uploader, ?string $manualVersion = null)
    {
        $tmp = $file->getRealPath();
        if (!$tmp || !is_file($tmp)) {
            return 'Berkas APK tidak terbaca. Coba unggah ulang.';
        }
        $info = ApkInspector::inspect($tmp);
        if ($info === null) {
            return 'Berkas bukan APK Android yang valid.';
        }
        if ($info['package'] !== self::PACKAGE) {
            return 'APK ini bukan aplikasi SISFOPERS KC (paket ' . $info['package'] . ').';
        }
        $version = $info['version_name'] !== '' ? $info['version_name'] : trim((string) $manualVersion);
        $version = preg_replace('/-.*$/', '', (string) $version);
        if (!preg_match('/^\d+(\.\d+){1,3}$/', $version)) {
            return 'Nomor versi APK tidak terbaca. Isi kolom versi (contoh 1.1.0).';
        }
        $prev = self::current();
        if ($prev && version_compare($version, (string) $prev['version'], '<')) {
            return "Versi {$version} lebih lama dari versi yang sudah diunggah ({$prev['version']}).";
        }

        $sha = hash_file('sha256', $tmp);
        $size = (int) filesize($tmp);
        $name = 'SISFOPERSKC-' . $version . '-' . substr($sha, 0, 8) . '.apk';
        $stored = Storage::disk(self::DISK)->putFileAs(self::DIR, $file, $name);
        if (!$stored) {
            return 'APK gagal disimpan di server (periksa izin folder storage).';
        }
        if ($prev && $prev['path'] !== $stored) {
            Storage::disk(self::DISK)->delete($prev['path']);
        }

        $meta = [
            'version' => $version,
            'version_code' => (int) $info['version_code'],
            'notes' => mb_substr(trim($notes), 0, 2000),
            'size' => $size,
            'sha256' => $sha,
            'path' => $stored,
            'uploaded_at' => now()->toIso8601String(),
            'uploaded_by' => $uploader,
            'notified' => 0,
        ];
        Setting::updateOrCreate(['key' => self::KEY], ['value' => json_encode($meta)]);
        return $meta;
    }

    public static function markNotified(int $count): void
    {
        $m = self::current();
        if ($m) {
            $m['notified'] = $count;
            Setting::updateOrCreate(['key' => self::KEY], ['value' => json_encode($m)]);
        }
    }

    public static function clear(): void
    {
        $m = self::current();
        if ($m) {
            Storage::disk(self::DISK)->delete($m['path']);
        }
        Setting::where('key', self::KEY)->delete();
    }

    /** Batas unggah efektif PHP (byte). */
    public static function uploadLimitBytes(): int
    {
        $toBytes = function ($v): int {
            $v = trim((string) $v);
            if ($v === '' || $v === '-1' || $v === '0') {
                return PHP_INT_MAX;
            }
            $n = (int) $v;
            return match (strtolower(substr($v, -1))) {
                'g' => $n * 1073741824,
                'm' => $n * 1048576,
                'k' => $n * 1024,
                default => $n,
            };
        };
        return min($toBytes(ini_get('upload_max_filesize')), $toBytes(ini_get('post_max_size')));
    }
}
