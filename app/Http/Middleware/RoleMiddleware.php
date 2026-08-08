<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::guard('web')->user() ?: Auth::guard('pju')->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (isset($user->is_active) && !$user->is_active) {
            Auth::guard('web')->logout();
            Auth::guard('pju')->logout();
            return redirect()->route('login')->withErrors(['username' => 'Akun Anda belum aktif atau ditangguhkan.']);
        }

        foreach ($roles as $role) {
            if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
    }
}