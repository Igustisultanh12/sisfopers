<?php

namespace App\Http\Controllers\Kordinator;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\BroadcastResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardKordinatorController extends Controller
{
    /**
     * Halaman Utama Dashboard Pembinaan Koordinator
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $myProfile = $user->personel;

        if (!$myProfile) {
            return redirect()->route('login')->withErrors(['username' => 'Akun Koordinator Anda tidak terikat dengan profil Personel. Silakan hubungi Admin.']);
        }

        $isAngkatan = $user->hasRole('kordinator_angkatan');
        $isMatra = $user->hasRole('kordinator_matra');

        $query = Personel::with(['user', 'sinyalmen', 'broadcastResponses.broadcast']);

        // Batasi cakupan data personel di bawah koordinasinya secara taktis
        if ($isAngkatan) {
            $query->where('angkatan', $myProfile->angkatan);
        } elseif ($isMatra) {
            $query->where('matra', $myProfile->matra);
        }

        // Fitur Pencarian & Filter Jajaran Anggota
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%')
                  ->orWhere('nikc', 'like', '%' . $search . '%');
            });
        }

        if ($isAngkatan && $request->filled('matra')) {
            $query->where('matra', $request->matra);
        } elseif ($isMatra && $request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        $jajaran = $query->latest()->paginate(10)->withQueryString();

        // 1. Data Agregasi Ringkasan (Widget Stat)
        $totalQuery = Personel::query();
        if ($isAngkatan) {
            $totalQuery->where('angkatan', $myProfile->angkatan);
        } elseif ($isMatra) {
            $totalQuery->where('matra', $myProfile->matra);
        }
        $totalCount = $totalQuery->count();
        
        $aktifCount = (clone $totalQuery)->whereHas('user', function($q) { $q->where('is_active', true); })->count();
        $verifiedCount = (clone $totalQuery)->where('face_verified', true)->count();
        
        // 2. Data Grafik Distribusi
        $distributionData = [];
        if ($isAngkatan) {
            // Koordinator Angkatan melihat distribusi Matra di angkatannya
            $dist = (clone $totalQuery)->select('matra', DB::raw('count(*) as total'))->groupBy('matra')->pluck('total', 'matra')->toArray();
            $distributionData = [
                'labels' => ['TNI AD', 'TNI AL', 'TNI AU'],
                'data' => [
                    $dist['AD'] ?? 0,
                    $dist['AL'] ?? 0,
                    $dist['AU'] ?? 0
                ]
            ];
        } elseif ($isMatra) {
            // Koordinator Matra melihat distribusi Angkatan di matranya
            $dist = (clone $totalQuery)->select('angkatan', DB::raw('count(*) as total'))->groupBy('angkatan')->orderBy('angkatan', 'ASC')->get();
            $distributionData = [
                'labels' => $dist->pluck('angkatan')->toArray(),
                'data' => $dist->pluck('total')->toArray()
            ];
        }

        return Inertia::render('Kordinator/Dashboard', [
            'myProfile' => $myProfile,
            'role' => $isAngkatan ? 'kordinator_angkatan' : 'kordinator_matra',
            'jajaran' => $jajaran,
            'stats' => [
                'total_personel' => $totalCount,
                'total_aktif' => $aktifCount,
                'total_verified' => $verifiedCount,
            ],
            'distribution' => $distributionData,
            'filters' => $request->only(['search', 'matra', 'angkatan'])
        ]);
    }

    /**
     * Memperbarui Catatan Pembinaan / Evaluasi Anggota
     */
    public function updateNotes(Request $request, $uuid)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        $user = Auth::user();
        $myProfile = $user->personel;
        $target = Personel::where('uuid', $uuid)->firstOrFail();

        // Keamanan: Validasi otoritas cakupan data koordinasi
        if ($user->hasRole('kordinator_angkatan') && $target->angkatan !== $myProfile->angkatan) {
            abort(403, 'Akses Ditolak: Personel berada di luar angkatan koordinasi Anda.');
        }

        if ($user->hasRole('kordinator_matra') && $target->matra !== $myProfile->matra) {
            abort(403, 'Akses Ditolak: Personel berada di luar matra koordinasi Anda.');
        }

        try {
            $target->update([
                'catatan_pembinaan' => $request->notes
            ]);

            return back()->with('success', 'Catatan pembinaan personel berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui catatan: ' . $e->getMessage()]);
        }
    }
}
