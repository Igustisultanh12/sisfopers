<?php

namespace App\Http\Middleware;

use App\Support\DeviceGuard;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencatat sesi web yang aktif (agar aplikasi Android dapat menanyakan "logout sesi web?")
 * dan mengakhiri sesi web yang sudah dicabut dari aplikasi.
 */
class TrackWebSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasSession()) {
            return $next($request);
        }

        foreach (['web', 'pju'] as $guard) {
            $actor = Auth::guard($guard)->user();
            if (!$actor) {
                continue;
            }
            $session = $request->session();
            $loginAt = (int) $session->get('web_login_at', 0);
            if ($loginAt === 0) {
                $loginAt = now()->timestamp;
                $session->put('web_login_at', $loginAt);
            }
            if ($loginAt <= DeviceGuard::webRevokedAt($actor)) {
                Auth::guard($guard)->logout();
                $session->invalidate();
                $session->regenerateToken();
                $msg = 'Sesi web Anda telah diakhiri karena akun ini masuk di aplikasi Android.';
                if ($request->expectsJson() && !$request->header('X-Inertia')) {
                    return response()->json(['success' => false, 'code' => 'unauthenticated', 'message' => $msg], 401);
                }
                return redirect()->route('login')->withErrors(['username' => $msg]);
            }
            DeviceGuard::markWebActive($actor);
        }

        return $next($request);
    }
}
