<?php

namespace App\Http\Middleware;

use App\Models\Pju;
use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Jembatan Aplikasi Mobile (Android) ke seluruh rute web SISFOPERSKC.
 *
 * Seluruh rute pada routes/web.php didaftarkan ulang di bawah prefix /api/m
 * dengan middleware ini, sehingga aplikasi mobile memakai logika controller,
 * validasi, dan hak akses yang SAMA PERSIS dengan versi web.
 *
 *  - Autentikasi memakai token Bearer (Laravel Sanctum) milik User atau PJU.
 *  - Respons Inertia dikembalikan sebagai JSON (komponen + props halaman).
 *  - Respons redirect (setelah POST/PUT/DELETE) dikonversi menjadi JSON
 *    berisi status sukses, pesan flash, dan pesan validasi.
 *  - Sesi berjalan di memori (driver array) sehingga tidak ada cookie/CSRF.
 */
class MobileBridge
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Inertia', 'true');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        // Driver default diganti ke array agar helper session() memakai store yang sama
        config(['session.driver' => 'array']);
        $session = app('session')->driver('array');
        $session->setId(Str::random(40));
        $session->start();
        $session->setPreviousUrl(url('/'));
        $request->setLaravelSession($session);
        app()->instance('session.store', $session);
        app('redirect')->setSession($session);

        $this->authenticateToken($request);

        // Pulihkan data sesi milik token (alur multi-langkah seperti OTP)
        $sessionKey = $this->sessionCacheKey($request);
        if ($sessionKey) {
            $stored = Cache::get($sessionKey);
            if (is_array($stored)) {
                foreach ($stored as $key => $value) {
                    $session->put($key, $value);
                }
            }
        }

        $response = $next($request);

        $result = $this->transform($request, $response, $session);

        if ($sessionKey) {
            $this->persistSession($sessionKey, $session);
        }

        return $result;
    }

    protected function sessionCacheKey(Request $request): ?string
    {
        $token = $request->attributes->get('mobile_token');
        return $token ? 'mobile_session:' . $token->id : null;
    }

    protected function persistSession(string $key, $session): void
    {
        try {
            $data = $session->all();
            $transient = array_merge(
                (array) ($data['_flash']['new'] ?? []),
                (array) ($data['_flash']['old'] ?? []),
                ['_flash', '_token', '_previous', 'errors', 'success', 'error', 'message', 'status', 'info', 'warning', 'ineligible_error', 'url']
            );
            foreach ($transient as $k) {
                unset($data[$k]);
            }
            if (empty($data)) {
                Cache::forget($key);
            } else {
                Cache::put($key, $data, now()->addHours(6));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function authenticateToken(Request $request): void
    {
        $bearer = $request->bearerToken();
        if (!$bearer) {
            return;
        }

        $token = PersonalAccessToken::findToken($bearer);
        if (!$token || ($token->expires_at && $token->expires_at->isPast())) {
            return;
        }

        $model = $token->tokenable;
        if (!$model) {
            return;
        }

        if (isset($model->is_active) && !$model->is_active) {
            return;
        }

        if ($model instanceof Pju) {
            Auth::guard('pju')->setUser($model);
        } elseif ($model instanceof User) {
            Auth::guard('web')->setUser($model);
            Auth::shouldUse('web');
        } else {
            return;
        }

        if (!$token->last_used_at || $token->last_used_at->lt(now()->subMinutes(5))) {
            $token->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        $request->attributes->set('mobile_token', $token);
    }

    protected function transform(Request $request, Response $response, $session): Response
    {
        if (!$response instanceof RedirectResponse) {
            return $response;
        }

        $target = $response->getTargetUrl();
        $path = '/' . ltrim((string) parse_url($target, PHP_URL_PATH), '/');
        $hasUser = Auth::guard('web')->check() || Auth::guard('pju')->check();

        $errorBag = $session->get('errors');
        $errors = $errorBag instanceof ViewErrorBag ? $errorBag->getBag('default')->toArray() : [];

        $flashKeys = (array) $session->get('_flash.new', []);
        $flash = [];
        foreach ($flashKeys as $key) {
            if ($key === 'errors') {
                continue;
            }
            $flash[$key] = $session->get($key);
        }

        $errorMessage = $session->get('error') ?? $session->get('ineligible_error');
        $successMessage = $session->get('success') ?? $session->get('message') ?? $session->get('status') ?? $session->get('info');

        // Rute gerbang wajib yang memerlukan tindakan khusus di aplikasi
        if ($path === '/login' && !$hasUser && $successMessage === null) {
            return new JsonResponse([
                'success' => false,
                'code' => 'unauthenticated',
                'message' => $this->firstError($errors) ?? 'Sesi Anda telah berakhir. Silakan login kembali.',
                'errors' => $errors ?: null,
            ], 401);
        }
        if ($path === '/maintenance') {
            return new JsonResponse([
                'success' => false,
                'code' => 'maintenance',
                'message' => 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
            ], 503);
        }
        $requestedGate = str_contains($request->path(), 'face-verification') || str_contains($request->path(), 'sinyalmen');
        if (!$requestedGate && str_ends_with($path, '/personel/face-verification') && $request->isMethod('GET')) {
            return new JsonResponse([
                'success' => false,
                'code' => 'needs_otp',
                'message' => 'Akun Anda perlu diverifikasi dengan OTP terlebih dahulu.',
            ], 409);
        }
        if (!$requestedGate && str_ends_with($path, '/personel/sinyalmen/lengkap') && $request->isMethod('GET')) {
            return new JsonResponse([
                'success' => false,
                'code' => 'needs_sinyalmen',
                'message' => $session->get('info') ?? 'Harap lengkapi data ciri fisik (Sinyalmen) Anda terlebih dahulu.',
            ], 409);
        }

        $ok = empty($errors) && empty($errorMessage);

        return new JsonResponse([
            'success' => $ok,
            'message' => $ok
                ? ($successMessage ?? 'Berhasil diproses.')
                : ($errorMessage ?? $this->firstError($errors) ?? 'Terjadi kesalahan.'),
            'errors' => $errors ?: null,
            'flash' => $flash ?: null,
            'redirect' => $path,
        ], $ok ? 200 : 422);
    }

    protected function firstError(array $errors): ?string
    {
        foreach ($errors as $messages) {
            if (is_array($messages) && isset($messages[0])) {
                return (string) $messages[0];
            }
        }
        return null;
    }
}
