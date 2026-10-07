<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EktaPersonelController extends Controller
{
    /**
     * Halaman E-KTA Personel (Dalam Masa Pengembangan)
     */
    public function index()
    {
        $user = auth()->user();
        $personel = $user ? $user->personel : null;

        if ($personel) {
            $personel->load(['sinyalmen', 'ekta']);
        }

        return Inertia::render('Personel/Ekta/Index', [
            'personel'               => $personel,
            'is_under_development'   => true,
            'development_notice'     => 'Mohon Maaf fitur ini dalam tahap pengembangan',
            'development_description'=> 'Layanan penerbitan dan unduhan Kartu Tanda Anggota Elektronik (E-KTA) Komponen Cadangan saat ini sedang dalam proses finalisasi integrasi digital oleh Administrator Mabes Komcad RI.',
        ]);
    }
}
