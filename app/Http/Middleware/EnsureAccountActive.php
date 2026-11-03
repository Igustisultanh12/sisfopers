<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tegakkan status keaktifan akun pada setiap request API.
 *
 * Login web mengecek is_active via RoleMiddleware di tiap request, tetapi jalur
 * API (auth:sanctum) hanya mengecek saat penerbitan token. Tanpa middleware ini,
 * token milik akun yang telah dinonaktifkan (mis. pengkinian MENINGGAL) tetap
 * dapat mengakses API. Middleware ini menolak dan mencabut token tsb.
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && isset($user->is_active) && !$user->is_active) {
            // Cabut token yang sedang dipakai agar tidak bisa dipakai lagi.
            $token = method_exists($user, 'currentAccessToken') ? $user->currentAccessToken() : null;
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }

            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan.',
            ], 403);
        }

        return $next($request);
    }
}
