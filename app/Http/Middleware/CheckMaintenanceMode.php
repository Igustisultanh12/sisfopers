<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class CheckMaintenanceMode
{
    /**
     * Memeriksa apakah aplikasi sedang dalam perbaikan (Under Maintenance).
     * Jika aktif, personel yang terautentikasi akan dialihkan ke halaman perbaikan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isMaintenance = Setting::where('key', 'under_maintenance')->value('value') === '1';

        if ($isMaintenance) {
            // Berlaku khusus untuk personel
            if (auth()->check() && auth()->user()->hasRole('personel')) {
                if (!$request->is('maintenance', 'logout', 'personel/logout')) {
                    return redirect()->route('maintenance');
                }
            }
        }

        return $next($request);
    }
}
