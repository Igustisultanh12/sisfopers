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

        // Konfigurasi kartu (logo, tanda tangan, stempel, pejabat, font) agar kartu di aplikasi sama dengan web
        $settings = \App\Models\Setting::whereIn('key', [
            'ekta_logo_komcad', 'ekta_signature', 'ekta_stamp', 'ekta_signer_name',
            'ekta_signer_rank', 'ekta_signer_title', 'ekta_font_family', 'ekta_font_size',
        ])->pluck('value', 'key')->toArray();

        return Inertia::render('Personel/Ekta/Index', [
            'ektaSettings'           => [
                'logo_komcad'  => $settings['ekta_logo_komcad'] ?? null,
                'signature'    => $settings['ekta_signature'] ?? null,
                'stamp'        => $settings['ekta_stamp'] ?? null,
                'signer_name'  => $settings['ekta_signer_name'] ?? 'Sri Yanto, S.T.',
                'signer_rank'  => $settings['ekta_signer_rank'] ?? 'Laksamana Muda TNI',
                'signer_title' => $settings['ekta_signer_title'] ?? 'Direktur Jenderal Potensi Pertahanan',
                'font_family'  => $settings['ekta_font_family'] ?? 'Arial',
                'font_size'    => $settings['ekta_font_size'] ?? '11px',
            ],
            'personel'               => $personel,
            'is_under_development'   => true,
            'development_notice'     => 'Mohon Maaf fitur ini dalam tahap pengembangan',
            'development_description'=> 'Layanan penerbitan dan unduhan Kartu Tanda Anggota Elektronik (E-KTA) Komponen Cadangan saat ini sedang dalam proses finalisasi integrasi digital oleh Administrator Mabes Komcad RI.',
        ]);
    }
}
