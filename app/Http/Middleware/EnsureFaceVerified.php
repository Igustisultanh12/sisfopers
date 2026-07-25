<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFaceVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Hanya jalankan proteksi jika user memiliki role 'personel'
            if ($user->hasRole('personel')) {
                if (!$user->personel || !$user->personel->face_verified) {
                    return redirect()->route('personel.face-verification');
                }
            }
        }
        return $next($request);
    }
}