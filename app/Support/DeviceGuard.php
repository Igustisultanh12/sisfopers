<?php

namespace App\Support;

use App\Models\MobileDevice;
use App\Models\Pju;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Satu akun hanya boleh aktif di satu perangkat Android, dan login aplikasi
 * menanyakan apakah sesi web yang masih aktif ingin diakhiri.
 *
 * - Token aplikasi bernama "android|{device_id}|{nama perangkat}".
 * - Aktivitas web dicatat middleware TrackWebSession di cache "web_active:{tipe}:{id}".
 * - Logout sesi web dari aplikasi menulis "web_revoked:{tipe}:{id}" sehingga sesi web
 *   lama ditolak pada permintaan berikutnya (berlaku untuk semua driver sesi).
 */
class DeviceGuard
{
    /** Token yang tidak dipakai selama ini dianggap perangkat lama yang sudah tidak aktif. */
    public const STALE_DAYS = 30;

    public static function key(Model $actor): string
    {
        return ($actor instanceof Pju ? 'pju' : 'user') . ':' . $actor->getKey();
    }

    public static function tokenName(string $deviceId, string $deviceName): string
    {
        return 'android|' . Str::limit($deviceId, 80, '') . '|' . Str::limit($deviceName, 100, '');
    }

    /** @return array{0: ?string, 1: string} [device_id, nama perangkat] dari nama token. */
    public static function parseTokenName(string $name): array
    {
        if (str_starts_with($name, 'android|')) {
            $parts = explode('|', $name, 3);
            return [$parts[1] ?? null, $parts[2] ?? 'Android'];
        }
        if (str_starts_with($name, 'android:')) {
            return [null, substr($name, 8) ?: 'Android'];
        }
        return [null, $name];
    }

    private static function mobileTokens(Model $actor)
    {
        return $actor->tokens()->where(function ($q) {
            $q->where('name', 'like', 'android|%')->orWhere('name', 'like', 'android:%');
        });
    }

    /** Perangkat lain yang masih memegang sesi aplikasi akun ini (token lama yang tidak aktif dibersihkan). */
    public static function otherDevice(Model $actor, string $deviceId): ?array
    {
        $staleBefore = now()->subDays(self::STALE_DAYS);
        foreach (self::mobileTokens($actor)->orderByDesc('last_used_at')->get() as $token) {
            [$tokenDevice, $name] = self::parseTokenName($token->name);
            $lastActive = $token->last_used_at ?? $token->created_at;
            if ($lastActive && $lastActive->lt($staleBefore)) {
                $token->delete();
                continue;
            }
            if ($tokenDevice !== null && hash_equals($tokenDevice, $deviceId)) {
                continue;
            }
            return [
                'name' => $name,
                'last_active' => optional($lastActive)->toIso8601String(),
            ];
        }
        return null;
    }

    /** Hapus token aplikasi milik perangkat yang sama (login ulang di HP yang sama). */
    public static function forgetSameDevice(Model $actor, string $deviceId): void
    {
        foreach (self::mobileTokens($actor)->get() as $token) {
            [$tokenDevice] = self::parseTokenName($token->name);
            if ($tokenDevice !== null && hash_equals($tokenDevice, $deviceId)) {
                $token->delete();
            }
        }
    }

    public static function markWebActive(Model $actor): void
    {
        Cache::put('web_active:' . self::key($actor), now()->timestamp, now()->addMinutes(max(5, (int) config('session.lifetime', 120))));
    }

    public static function webActive(Model $actor): bool
    {
        $k = self::key($actor);
        $active = (int) Cache::get('web_active:' . $k, 0);
        $revoked = (int) Cache::get('web_revoked:' . $k, 0);
        if ($active > 0 && $active > $revoked) {
            return true;
        }
        // Driver sesi database: periksa sesi web akun User yang masih hidup
        if ($actor instanceof User && config('session.driver') === 'database') {
            try {
                $minTime = now()->subMinutes((int) config('session.lifetime', 120))->timestamp;
                return DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $actor->getKey())
                    ->where('last_activity', '>=', max($minTime, $revoked + 1))
                    ->exists();
            } catch (\Throwable $e) {
                return false;
            }
        }
        return false;
    }

    public static function webRevokedAt(Model $actor): int
    {
        return (int) Cache::get('web_revoked:' . self::key($actor), 0);
    }

    /** Akhiri seluruh sesi web akun ini. */
    public static function logoutWeb(Model $actor): void
    {
        $k = self::key($actor);
        Cache::put('web_revoked:' . $k, now()->timestamp, now()->addDays(60));
        Cache::forget('web_active:' . $k);
        try {
            // cookie "ingat saya" lama tidak berlaku lagi
            $actor->setRememberToken(Str::random(60));
            $actor->timestamps = false;
            $actor->save();
        } catch (\Throwable $e) {
            Log::warning('DeviceGuard remember token: ' . $e->getMessage());
        }
        if ($actor instanceof User && config('session.driver') === 'database') {
            try {
                DB::table(config('session.table', 'sessions'))->where('user_id', $actor->getKey())->delete();
            } catch (\Throwable $e) {
                // abaikan
            }
        }
    }

    /** Lepaskan semua perangkat aplikasi akun ini (dipakai alur "kehilangan akses HP lama"). */
    public static function releaseAllDevices(Model $actor): int
    {
        $type = get_class($actor);
        $fcm = MobileDevice::where('tokenable_type', $type)->where('tokenable_id', $actor->getKey())->pluck('fcm_token')->all();
        if ($fcm && FcmService::enabled()) {
            try {
                FcmService::sendToTokens(
                    $fcm,
                    'Perangkat dilepas dari akun',
                    'Akun SISFOPERS KC Anda telah dilepas dari perangkat ini. Silakan login kembali bila diperlukan.',
                    ['url' => '/login'],
                    'device_released'
                );
            } catch (\Throwable $e) {
                Log::warning('DeviceGuard push: ' . $e->getMessage());
            }
        }
        $count = self::mobileTokens($actor)->count();
        self::mobileTokens($actor)->delete();
        MobileDevice::where('tokenable_type', $type)->where('tokenable_id', $actor->getKey())->delete();
        return $count;
    }

    public static function maskEmail(?string $email): ?string
    {
        if (!$email || !str_contains($email, '@')) {
            return null;
        }
        [$name, $domain] = explode('@', $email, 2);
        $n = mb_strlen($name);
        $shown = $n <= 2 ? mb_substr($name, 0, 1) : mb_substr($name, 0, 2);
        return $shown . str_repeat('*', max(3, $n - mb_strlen($shown))) . '@' . $domain;
    }

    /** Cari akun berdasarkan NIKC/username/email (User lalu PJU). */
    public static function findActor(string $identity): ?Model
    {
        $identity = trim($identity);
        if ($identity === '') {
            return null;
        }
        $user = User::where('username', $identity)->first()
            ?? User::where('email', strtolower($identity))->first()
            ?? optional(\App\Models\Personel::where('nikc', $identity)->first())->user;
        if ($user) {
            return $user;
        }
        return Pju::where('username', $identity)->first() ?? Pju::where('email', strtolower($identity))->first();
    }
}
