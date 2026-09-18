<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardPersonelController extends Controller
{
    /**
     * Menampilkan Halaman Dashboard Utama Personel Komcad (Eager Loading Komplit)
     */
    public function index()
    {
        $user = Auth::user();

        // Mengambil data personel terikat beserta relasi user akun dan sinyalmen fisik
        $personel = Personel::with(['user', 'sinyalmen'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $personel->ensureKomcadEducationExists();

        // Mengambil statistik respon kehadiran personel secara riil berdasarkan relasi log kegiatan
        $totalKegiatan = $personel->broadcastResponses()->count();
        $totalHadir    = $personel->broadcastResponses()->where('status', 'HADIR')->count();
        $totalIzin     = $personel->broadcastResponses()->where('status', 'IZIN')->count();

        // Mengambil 3 pengumuman / broadcast kegiatan terbaru untuk ditampilkan di HP
        $latestBroadcasts = \App\Models\Broadcast::whereHas('targets', function($q) use ($personel) {
                $q->where('personel_id', $personel->id);
            })
            ->orWhere('matra', $personel->matra)
            ->orWhere('matra', 'ALL')
            ->latest()
            ->take(3)
            ->get();

        // Mengambil formulir / rekrutmen aktif yang ditujukan kepada personel ini dan belum diisi
        $activeForms = \App\Models\CustomForm::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('deadline')->orWhere('deadline', '>=', now());
            })
            ->latest()
            ->get()
            ->filter(function($form) use ($personel) {
                return $form->isPersonelEligible($personel) && !$form->hasPersonelSubmitted($personel);
            })
            ->values();

        return Inertia::render('Personel/Dashboard', [
            'personel' => $personel,
            'stats' => [
                'total_kegiatan' => $totalKegiatan,
                'total_hadir'    => $totalHadir,
                'total_izin'     => $totalIzin,
            ],
            'latestBroadcasts' => $latestBroadcasts,
            'activeForms' => $activeForms,
        ]);
    }
}