<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Memastikan Personel Komponen Cadangan Telah Melengkapi Data Sinyalmen Fisik
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Hanya jalankan proteksi jika user memiliki role 'personel'
            if ($user->hasRole('personel')) {
                
                // SOLUSI INTI: Pengecualian rute agar tidak terjadi ERR_TOO_MANY_REDIRECTS
                if ($request->routeIs('personel.sinyalmen.create') || $request->routeIs('personel.sinyalmen.store')) {
                    return $next($request);
                }

                // Jika data personel belum ada, atau status profilnya belum bermutasi ke 'LENGKAP'
                if (!$user->personel || $user->personel->status_profile !== 'LENGKAP') {
                    return redirect()->route('personel.sinyalmen.create')
                        ->with('info', 'Harap lengkapi data ciri fisik (Sinyalmen) Anda terlebih dahulu.');
                }
            }
        }
        
        return $next($request);
    }
}