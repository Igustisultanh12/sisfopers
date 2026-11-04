<?php

namespace App\Services;

use App\Models\MobileDevice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pengirim Firebase Cloud Messaging (HTTP v1) tanpa dependensi tambahan.
 * Dikonfigurasi admin melalui Pengaturan Sistem › Notifikasi Push Aplikasi.
 */
class FcmService
{
    protected static ?array $settingsCache = null;

    protected static function settings(): array
    {
        if (self::$settingsCache === null) {
            try {
                self::$settingsCache = \App\Models\Setting::where('key', 'like', 'fcm_%')->pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                self::$settingsCache = [];
            }
        }
        return self::$settingsCache;
    }

    public static function flushSettings(): void
    {
        self::$settingsCache = null;
    }

    /**
     * Path berkas service account: diunggah lewat Pengaturan Sistem admin,
     * atau (cadangan) env FIREBASE_CREDENTIALS.
     */
    public static function credentialsPath(): string
    {
        $stored = self::settings()['fcm_service_account_path'] ?? null;
        if ($stored) {
            return storage_path('app/private/' . ltrim($stored, '/'));
        }
        return (string) config('services.fcm.credentials', '');
    }

    public static function enabled(): bool
    {
        $settings = self::settings();
        if (array_key_exists('fcm_enabled', $settings) && $settings['fcm_enabled'] !== '1') {
            return false;
        }
        $path = self::credentialsPath();
        return $path !== '' && is_file($path) && is_readable($path);
    }

    /**
     * Konfigurasi klien Firebase yang dibaca aplikasi Android saat dibuka.
     */
    public static function clientConfig(): ?array
    {
        $s = self::settings();
        if (($s['fcm_enabled'] ?? '0') !== '1' || empty($s['fcm_android_app_id']) || empty($s['fcm_api_key']) || empty($s['fcm_project_id'])) {
            return null;
        }
        return [
            'project_id' => $s['fcm_project_id'],
            'application_id' => $s['fcm_android_app_id'],
            'api_key' => $s['fcm_api_key'],
            'sender_id' => $s['fcm_sender_id'] ?? '',
            'storage_bucket' => $s['fcm_storage_bucket'] ?? '',
        ];
    }

    public static function status(): array
    {
        self::flushSettings();
        $s = self::settings();
        return [
            'enabled' => ($s['fcm_enabled'] ?? '0') === '1',
            'server_ready' => self::enabled(),
            'client_ready' => self::clientConfig() !== null,
            'client_email' => $s['fcm_client_email'] ?? null,
            'server_project_id' => $s['fcm_server_project_id'] ?? null,
            'project_id' => $s['fcm_project_id'] ?? null,
            'package_name' => $s['fcm_package_name'] ?? null,
            'devices' => \App\Models\MobileDevice::count(),
        ];
    }

    protected static function credentials(): ?array
    {
        if (!self::enabled()) {
            return null;
        }
        $json = json_decode((string) file_get_contents(self::credentialsPath()), true);
        if (!is_array($json) || empty($json['client_email']) || empty($json['private_key']) || empty($json['project_id'])) {
            return null;
        }
        return $json;
    }

    protected static function accessToken(array $cred): ?string
    {
        $cacheKey = 'fcm:access_token:' . md5($cred['client_email']);

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($cred) {
            $now = time();
            $header = self::b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claims = self::b64(json_encode([
                'iss' => $cred['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $cred['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));
            $signature = '';
            if (!openssl_sign($header . '.' . $claims, $signature, $cred['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new \RuntimeException('Gagal menandatangani JWT FCM.');
            }
            $jwt = $header . '.' . $claims . '.' . self::b64($signature);

            $res = Http::asForm()->timeout(10)->post($cred['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);
            if (!$res->successful()) {
                throw new \RuntimeException('Gagal memperoleh access token FCM: ' . $res->body());
            }
            return $res->json('access_token');
        });
    }

    protected static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Kirim pesan data (high priority) ke seluruh perangkat milik penerima.
     *
     * @param string $type notification|chat|call|vicon|broadcast
     */
    public static function sendToNotifiable(string $notifiableType, int $notifiableId, string $title, string $body, array $data = [], string $type = 'notification'): array
    {
        if (!self::enabled()) {
            return ['sent' => 0, 'error' => 'FCM tidak aktif.'];
        }

        $tokens = MobileDevice::where('tokenable_type', $notifiableType)
            ->where('tokenable_id', $notifiableId)
            ->pluck('fcm_token')
            ->all();

        return self::sendToTokens($tokens, $title, $body, $data, $type);
    }

    public static function sendToTokens(array $tokens, string $title, string $body, array $data = [], string $type = 'notification'): array
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        if (empty($tokens)) {
            return ['sent' => 0, 'error' => 'Tidak ada perangkat terdaftar.'];
        }

        $cred = self::credentials();
        if (!$cred) {
            return ['sent' => 0, 'error' => 'Berkas service account tidak valid.'];
        }

        try {
            $accessToken = self::accessToken($cred);
        } catch (\Throwable $e) {
            Cache::forget('fcm:access_token:' . md5($cred['client_email']));
            Log::warning('FCM: ' . $e->getMessage());
            return ['sent' => 0, 'error' => $e->getMessage()];
        }
        if (!$accessToken) {
            return ['sent' => 0, 'error' => 'Access token kosong.'];
        }
        $sent = 0;
        $lastError = null;

        $payload = array_merge($data, [
            'type' => $type,
            'title' => mb_substr($title, 0, 200),
            'body' => mb_substr($body, 0, 1000),
            'sent_at' => now()->toIso8601String(),
        ]);
        $payload = array_map(fn ($v) => is_scalar($v) || $v === null ? (string) $v : json_encode($v), $payload);

        $endpoint = 'https://fcm.googleapis.com/v1/projects/' . $cred['project_id'] . '/messages:send';

        foreach ($tokens as $token) {
            try {
                $res = Http::withToken($accessToken)->timeout(10)->post($endpoint, [
                    'message' => [
                        'token' => $token,
                        'data' => $payload,
                        'android' => [
                            'priority' => 'HIGH',
                            'ttl' => $type === 'call' ? '45s' : '86400s',
                        ],
                    ],
                ]);

                if ($res->successful()) {
                    $sent++;
                } else {
                    $lastError = $res->json('error.message') ?? $res->body();
                    $status = $res->json('error.status');
                    $code = $res->json('error.details.0.errorCode');
                    if (in_array($status, ['NOT_FOUND', 'UNREGISTERED'], true) || $code === 'UNREGISTERED') {
                        MobileDevice::where('fcm_token', $token)->delete();
                    } else {
                        Log::warning('FCM gagal mengirim: ' . $res->body());
                    }
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning('FCM error: ' . $e->getMessage());
            }
        }

        return ['sent' => $sent, 'error' => $lastError];
    }
}
