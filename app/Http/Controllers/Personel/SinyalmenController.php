<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\Sinyalmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SinyalmenController extends Controller
{
    /**
     * Menampilkan Form Isian Karakter Fisik Sinyalmen
     */
    public function create()
    {
        $user = Auth::user();
        $personel = $user->personel;

        // Validasi Alur Keamanan 1: Jika belum scan wajah, paksa mundur ke gerbang 1
        if (!$personel || !$personel->face_verified) {
            return redirect()->route('personel.face-verification');
        }

        // Validasi Alur Keamanan 2: Jika sudah punya sinyalmen, jangan boleh isi lagi, dorong ke dashboard
        if ($personel->sinyalmen) {
            return redirect()->route('personel.dashboard');
        }

        return Inertia::render('Personel/Sinyalmen');
    }

    /**
     * Memproses Penyimpanan Ciri Fisik Pasukan
     */
    public function store(Request $request)
    {
        $request->validate([
            'tinggi_badan' => 'required|integer|min:100|max:250',
            'berat_badan' => 'required|integer|min:30|max:200',
            'golongan_darah' => 'required|string|in:A,B,AB,O',
            'rambut' => 'required|string|max:255',
            'mata' => 'required|string|max:255',
            'ciri_khas' => 'required|string|max:255',
            'cacat_tubuh' => 'required|string',
        ]);

        $user = Auth::user();
        $personel = $user->personel;

        if (!$personel) {
            return back()->withErrors(['error' => 'Gagal mendeteksi otorisasi akun personel.']);
        }

        try {
            // Memasukkan data ciri fisik ke tabel sinyalmen
            Sinyalmen::create([
                'personel_id' => $personel->id,
                'tinggi_badan' => $request->tinggi_badan,
                'berat_badan' => $request->berat_badan,
                'golongan_darah' => $request->golongan_darah,
                'rambut' => $request->rambut,
                'mata' => $request->mata,
                'ciri_khas' => $request->ciri_khas,
                'cacat_tubuh' => $request->cacat_tubuh,
            ]);

            // Sekaligus amandemen status kelengkapan profil personel menjadi LENGKAP
            $personel->update([
                'status_profile' => 'LENGKAP'
            ]);

            // Sukses besar! Alur validasi selesai, arahkan pasukan langsung masuk ke Dashboard Utama
            return redirect()->route('personel.dashboard');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal mengunci enkripsi data fisik: ' . $e->getMessage()]);
        }
    }
}