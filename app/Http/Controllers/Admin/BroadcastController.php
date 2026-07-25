<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\BroadcastTarget;
use App\Models\Personel;
use App\Jobs\SendWhatsappNotificationJob;
use App\Notifications\NewBroadcastNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    /**
     * Menampilkan Daftar Riwayat Distribusi Maklumat & Mobilisasi Pasukan
     */
    public function index()
    {
        $broadcasts = Broadcast::withCount(['targets', 'responses'])->latest()->paginate(10);
        return Inertia::render('Admin/Broadcast/Index', ['broadcasts' => $broadcasts]);
    }

    /**
     * Menampilkan Formulir Pembuatan Komando Kegiatan Baru
     */
    public function create()
    {
        return Inertia::render('Admin/Broadcast/Create');
    }

    /**
     * Mengeksekusi Penyimpanan Instruksi Baru & Menyebarkan ke Target Anggota
     */
    public function store(Request $request)
    {
        // SINKRONISASI: Aturan validasi diselaraskan dengan input lowercase/uppercase Vue Form
        $validated = $request->validate([
            'title'        => 'required|string|max:200',
            'category'     => 'required|in:latihan,apel,mobilisasi,pengumuman,Tugas,Latihan,Mobilisasi', 
            'event_date'   => 'required|date',
            'event_time'   => 'required',
            'location'     => 'required|string|max:255',
            'description'  => 'required|string',
            'deadline'     => 'required', // Dibuat lebih fleksibel menerima string ISO format datetime-local
            'target_type'  => 'required|in:ALL,MATRA,ANGKATAN',
            'target_value' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan Induk Informasi Broadcast
            $broadcast = Broadcast::create(array_merge($validated, [
                'uuid' => (string) Str::uuid(),
                'created_by' => auth()->id()
            ]));

            // 2. Kumpulkan Koleksi Personel Berdasarkan Filter Target
            $query = Personel::with('user')->where('face_verified', true); // Eager load user akun untuk notifikasi
            
            if ($validated['target_type'] === 'MATRA') {
                $query->where('matra', $validated['target_value']);
            } elseif ($validated['target_type'] === 'ANGKATAN') {
                $query->where('angkatan', $validated['target_value']);
            }
            
            $targetPersonels = $query->get();

            // 3. Distribusikan ke Target, Kirim Notifikasi Web & Bulk Email Notification (Tanpa WhatsApp agar tidak terdeteksi SPAM)
            foreach ($targetPersonels as $personel) {
                BroadcastTarget::create([
                    'broadcast_id' => $broadcast->id,
                    'personel_id'  => $personel->id
                ]);

                // Kirim Notifikasi Aplikasi & Email Massal ke Akun Personel
                if ($personel->user) {
                    try {
                        $personel->user->notify(new NewBroadcastNotification($broadcast));
                    } catch (\Exception $e) {
                        Log::error("Gagal kirim email broadcast ke " . ($personel->user->email ?? 'unknown') . ": " . $e->getMessage());
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.broadcast.index')->with('success', 'Instruksi broadcast kegiatan berhasil dikirimkan via Email Massal ke ' . $targetPersonels->count() . ' personel.');
        
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Broadcast: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memproses pengiriman broadcast: ' . $e->getMessage()]);
        }
    }

    /**
     * Menampilkan Dasbor Pemantauan Grafik Kehadiran Anggota Jajaran
     */
    public function show($uuid)
    {
        $broadcast = Broadcast::where('uuid', $uuid)->with(['responses.personel'])->firstOrFail();
        
        $stats = [
            'total'       => BroadcastTarget::where('broadcast_id', $broadcast->id)->count(),
            'hadir'       => $broadcast->responses->where('status_attendance', 'HADIR')->count(),
            'izin'        => $broadcast->responses->where('status_attendance', 'IZIN')->count(),
            'tidak_hadir' => $broadcast->responses->where('status_attendance', 'TIDAK_HADIR')->count(),
        ];

        return Inertia::render('Admin/Broadcast/Show', [
            'broadcast' => $broadcast,
            'stats'     => $stats
        ]);
    }

    public function edit($uuid)
    {
        $broadcast = Broadcast::where('uuid', $uuid)->firstOrFail();
        return Inertia::render('Admin/Broadcast/Edit', [
            'broadcast' => $broadcast
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $broadcast = Broadcast::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'title'        => 'required|string|max:200',
            'category'     => 'required|in:latihan,apel,mobilisasi,pengumuman,Tugas,Latihan,Mobilisasi', 
            'event_date'   => 'required|date',
            'event_time'   => 'required',
            'location'     => 'required|string|max:255',
            'description'  => 'required|string',
            'deadline'     => 'required',
            'target_type'  => 'required|in:ALL,MATRA,ANGKATAN',
            'target_value' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $broadcast->update($validated);

            // Bersihkan target lama dan sinkronkan target baru
            BroadcastTarget::where('broadcast_id', $broadcast->id)->delete();

            $query = Personel::with('user')->where('face_verified', true);
            if ($validated['target_type'] === 'MATRA') {
                $query->where('matra', $validated['target_value']);
            } elseif ($validated['target_type'] === 'ANGKATAN') {
                $query->where('angkatan', $validated['target_value']);
            }
            $targetPersonels = $query->get();

            foreach ($targetPersonels as $personel) {
                BroadcastTarget::create([
                    'broadcast_id' => $broadcast->id,
                    'personel_id'  => $personel->id
                ]);
            }

            DB::commit();
            return redirect()->route('admin.broadcast.index')->with('success', 'Broadcast kegiatan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Update Broadcast: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memperbarui broadcast: ' . $e->getMessage()]);
        }
    }

    /**
     * LENGKAPAN UTAMA: Menghapus Data Broadcast & Log Target Terkait (Cascading Safe)
     */
    public function destroy($uuid)
    {
        DB::beginTransaction();
        try {
            $broadcast = Broadcast::where('uuid', $uuid)->firstOrFail();
            
            // Bersihkan data target jajaran terdampak agar tidak menjadi data sampah (Orphan Data)
            BroadcastTarget::where('broadcast_id', $broadcast->id)->delete();
            
            // Hapus data induk komando
            $broadcast->delete();

            DB::commit();
            return redirect()->route('admin.broadcast.index')->with('success', 'Maklumat perintah kegiatan berhasil ditarik dan dihapus dari sistem.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Hapus Broadcast: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menghapus data broadcast: ' . $e->getMessage()]);
        }
    }
}