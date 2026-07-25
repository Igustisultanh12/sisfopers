<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\BroadcastResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Ditambahkan untuk penanganan data relasi taktis jika diperlukan
use Inertia\Inertia;

class BroadcastResponseController extends Controller
{
    /**
     * Menampilkan Daftar Lembar Kegiatan Mobilisasi Pasukan
     */
    public function index()
    {
        $user = Auth::user();
        $personel = $user->personel;

        if (!$personel) {
            return redirect()->route('personel.dashboard')->with('error', 'Profil personel Anda belum terkonfigurasi dengan lengkap.');
        }

        // Menampilkan broadcast kegiatan yang relevan dengan matra personel tersebut
        $broadcasts = Broadcast::where('matra', $personel->matra)
            ->orWhere('matra', 'ALL')
            ->with(['responses' => function($q) use ($personel) {
                $q->where('personel_id', $personel->id);
            }])
            ->latest()
            ->paginate(10);

        return Inertia::render('Personel/Broadcast/Index', [
            'broadcasts' => $broadcasts
        ]);
    }

    /**
     * LENGKAPAN UTAMA: Menampilkan Lembar Detail Instruksi Tugas Komando & Status Respon
     */
    public function show($uuid)
    {
        // 1. Ambil data induk instruksi kegiatan berdasarkan parameter UUID dari notifikasi
        $broadcast = Broadcast::where('uuid', $uuid)->firstOrFail();

        // 2. Tarik informasi data diri personel yang sedang aktif
        $personel = Auth::user()->personel;

        if (!$personel) {
            return redirect()->route('personel.dashboard')->with('error', 'Akses ditolak, profil administrasi Anda tidak ditemukan.');
        }

        // 3. Ambil data rekam jejak presensi yang pernah diisi sebelumnya oleh personel ini
        $existingResponse = BroadcastResponse::where('broadcast_id', $broadcast->id)
            ->where('personel_id', $personel->id)
            ->first();

        // Render ke komponen halaman detail sisi jajaran anggota
        return Inertia::render('Personel/Broadcast/Show', [
            'broadcast'         => $broadcast,
            'existing_response' => $existingResponse
        ]);
    }

    /**
     * Memproses Konfirmasi Presensi Kehadiran Personel (SINKRONISASI SCHEMA MYSQL)
     */
    public function respond(Request $request, $uuid)
    {
        $request->validate([
            'status' => 'required|in:HADIR,IZIN,ABSEN,TIDAK_HADIR',
            'notes'  => 'nullable|string|max:255',
            'permit_letter' => 'nullable|boolean'
        ]);

        $user = Auth::user();
        $personel = $user->personel;
        $broadcast = Broadcast::where('uuid', $uuid)->firstOrFail();

        BroadcastResponse::updateOrCreate(
            [
                'broadcast_id' => $broadcast->id,
                'personel_id'  => $personel->id,
            ],
            [
                'status_attendance' => $request->status, // Diubah dari 'status' ke 'status_attendance'
                'notes'             => $request->notes,
                'permit_letter'     => filter_var($request->permit_letter, FILTER_VALIDATE_BOOLEAN) ? 'YA' : 'TIDAK',
                'responded_at'      => now(),
                'created_at'        => now()
            ]
        );

        // Kirim notifikasi WA ke seluruh Admin & Koordinator
        $adminsAndCoordinators = \App\Models\User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'kordinator_angkatan', 'kordinator_matra']))
            ->with('personel')
            ->get();
            
        foreach ($adminsAndCoordinators as $recipientUser) {
            if ($recipientUser->personel && $recipientUser->personel->phone_number) {
                // Lewati jika mengirim ke diri sendiri
                if ($recipientUser->personel->id === $personel->id) {
                    continue;
                }
                
                $msgAdmin = "🔔 *RESPON KEHADIRAN BARU*\n\n"
                    . "Kegiatan: {$broadcast->title}\n"
                    . "Personel: {$personel->full_name} ({$personel->pangkat})\n"
                    . "Status: {$request->status}\n"
                    . "Catatan: " . ($request->notes ?? '-') . "\n"
                    . "Minta Izin: " . (filter_var($request->permit_letter, FILTER_VALIDATE_BOOLEAN) ? 'YA' : 'TIDAK') . "\n\n"
                    . "Silakan login ke platform SISFOPERS untuk meninjau rekapitulasi presensi.";
                \App\Services\WhatsappService::sendMessage($recipientUser->personel->phone_number, $msgAdmin);
            }
        }

        $user->notify(new \App\Notifications\SystemNotification(
            'Presensi Berhasil Terkirim',
            "Anda berhasil mengirimkan konfirmasi presensi untuk kegiatan: {$broadcast->title}.",
            'success',
            route('personel.broadcast.show', $broadcast->uuid)
        ));

        return back()->with('success', 'Konfirmasi lembar kehadiran Anda berhasil dikirimkan ke sistem pusat.');
    }
}