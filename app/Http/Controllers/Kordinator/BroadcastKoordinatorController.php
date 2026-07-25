<?php

namespace App\Http\Controllers\Kordinator;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\BroadcastTarget;
use App\Models\Personel;
use App\Jobs\SendWhatsappNotificationJob;
use App\Notifications\NewBroadcastNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class BroadcastKoordinatorController extends Controller
{
    /**
     * Menampilkan daftar riwayat broadcast yang dibuat oleh Koordinator Matra & Angkatan
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->hasRole('kordinator_matra') && !$user->hasRole('kordinator_angkatan')) {
            abort(403, 'Akses Ditolak: Fitur ini hanya tersedia bagi Koordinator.');
        }

        $broadcasts = Broadcast::where('created_by', $user->id)
            ->withCount(['targets', 'responses'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Kordinator/Broadcast/Index', [
            'broadcasts' => $broadcasts
        ]);
    }

    /**
     * Menampilkan form pembuatan broadcast baru
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user->hasRole('kordinator_matra') && !$user->hasRole('kordinator_angkatan')) {
            abort(403, 'Akses Ditolak: Fitur ini hanya tersedia bagi Koordinator.');
        }

        $myProfile = $user->personel;

        return Inertia::render('Kordinator/Broadcast/Create', [
            'myProfile' => $myProfile
        ]);
    }

    /**
     * Menyimpan dan menyebarkan broadcast baru khusus untuk personel matra/angkatan terkait
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('kordinator_matra') && !$user->hasRole('kordinator_angkatan')) {
            abort(403, 'Akses Ditolak: Fitur ini hanya tersedia bagi Koordinator.');
        }

        $myProfile = $user->personel;
        if (!$myProfile) {
            abort(403, 'Profil personel Anda tidak terkonfigurasi.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'category'    => 'required|in:latihan,apel,mobilisasi,pengumuman,Tugas,Latihan,Mobilisasi', 
            'event_date'  => 'required|date',
            'event_time'  => 'required',
            'location'    => 'required|string|max:255',
            'description' => 'required|string',
            'deadline'    => 'required',
        ]);

        $isMatra = $user->hasRole('kordinator_matra');

        if ($isMatra) {
            $targetType = 'MATRA';
            $targetValue = $myProfile->matra;
            $broadcastMatra = $myProfile->matra;
        } else {
            $targetType = 'ANGKATAN';
            $targetValue = $myProfile->angkatan;
            $broadcastMatra = 'ALL';
        }

        DB::beginTransaction();
        try {
            // 1. Simpan Broadcast dengan menargetkan kriteria koordinator secara otomatis
            $broadcast = Broadcast::create(array_merge($validated, [
                'uuid' => (string) Str::uuid(),
                'created_by' => $user->id,
                'target_type' => $targetType,
                'target_value' => $targetValue,
                'matra' => $broadcastMatra
            ]));

            // 2. Cari seluruh personel terverifikasi di bawah koordinasi yang sama
            $query = Personel::with('user')->where('face_verified', true);
            if ($isMatra) {
                $query->where('matra', $myProfile->matra);
            } else {
                $query->where('angkatan', $myProfile->angkatan);
            }
            $targetPersonels = $query->get();

            // 3. Distribusikan target dan kirim notifikasi
            foreach ($targetPersonels as $personel) {
                BroadcastTarget::create([
                    'broadcast_id' => $broadcast->id,
                    'personel_id'  => $personel->id
                ]);

                if ($personel->user) {
                    $personel->user->notify(new NewBroadcastNotification($broadcast));
                }

                // Format pesan WA
                if ($isMatra) {
                    $waMessage = "📢 *INSTRUKSI MATRA BARU: {$broadcast->title}*\n\n"
                        . "Kategori: " . strtoupper($broadcast->category) . "\n"
                        . "Waktu: " . date('d-m-Y', strtotime($broadcast->event_date)) . " | Pukul {$broadcast->event_time} WIB\n"
                        . "Lokasi: {$broadcast->location}\n\n"
                        . "Deskripsi:\n{$broadcast->description}\n\n"
                        . "⚠️ *Batas Waktu Konfirmasi:* " . date('d-m-Y H:i', strtotime($broadcast->deadline)) . " WIB\n\n"
                        . "Pengirim: Koordinator Matra " . ($myProfile->matra === 'AD' ? 'Darat' : ($myProfile->matra === 'AL' ? 'Laut' : 'Udara')) . "\n\n"
                        . "Silakan login ke platform SISFOPERSKC untuk mengisi lembar kehadiran Anda.";
                } else {
                    $waMessage = "📢 *INSTRUKSI ANGKATAN BARU: {$broadcast->title}*\n\n"
                        . "Kategori: " . strtoupper($broadcast->category) . "\n"
                        . "Waktu: " . date('d-m-Y', strtotime($broadcast->event_date)) . " | Pukul {$broadcast->event_time} WIB\n"
                        . "Lokasi: {$broadcast->location}\n\n"
                        . "Deskripsi:\n{$broadcast->description}\n\n"
                        . "⚠️ *Batas Waktu Konfirmasi:* " . date('d-m-Y H:i', strtotime($broadcast->deadline)) . " WIB\n\n"
                        . "Pengirim: Koordinator Angkatan " . $myProfile->angkatan . "\n\n"
                        . "Silakan login ke platform SISFOPERSKC untuk mengisi lembar kehadiran Anda.";
                }

                dispatch(new SendWhatsappNotificationJob($personel->phone_number, $waMessage));
            }

            DB::commit();

            $successMsg = $isMatra 
                ? 'Broadcast instruksi khusus matra berhasil dikirimkan ke ' . $targetPersonels->count() . ' personel.'
                : 'Broadcast instruksi khusus angkatan berhasil dikirimkan ke ' . $targetPersonels->count() . ' personel.';

            return redirect()->route('kordinator.broadcast.index')->with('success', $successMsg);
        
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Kordinator Broadcast: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memproses pengiriman broadcast: ' . $e->getMessage()]);
        }
    }
}
