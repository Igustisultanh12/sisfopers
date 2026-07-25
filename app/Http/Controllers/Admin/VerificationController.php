<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\Registration;
use App\Models\User;
use App\Jobs\SendWhatsappNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VerificationController extends Controller
{
    /**
     * Menampilkan Antrean Pendaftar Komponen Cadangan Baru
     */
    public function index(Request $request)
    {
        $query = Personel::whereHas('registration', function ($q) {
            $q->where('status_verification', 'PENDING');
        })->with(['user', 'registration', 'jobHistories']);

        // Handler Filter Pencarian Taktis
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('matra')) {
            $query->where('matra', $request->matra);
        }

        $pendaftar = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Verification/Index', [
            'pendaftar' => $pendaftar,
            'filters' => $request->only(['search', 'matra'])
        ]);
    }

    /**
     * Menampilkan Berkas & Detail Lembar Pendaftaran Personel
     */
    public function show($uuid)
    {
        $personel = Personel::where('uuid', $uuid)->with(['user', 'registration'])->firstOrFail();
        
        return Inertia::render('Admin/Verification/Show', [
            'pendaftar' => $personel
        ]);
    }

    /**
     * Memproses Keputusan Validasi: Setujui (APPROVED) / Tolak (REJECTED)
     */
    public function verify(Request $request, $uuid)
    {
        $request->validate([
            'status' => 'required|in:APPROVED,REJECTED',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $personel = Personel::where('uuid', $uuid)->firstOrFail();
            $registration = Registration::where('personel_id', $personel->id)->firstOrFail();

            $registration->update([
                'status_verification' => $request->status,
                'admin_notes' => $request->admin_notes,
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            if ($request->status === 'APPROVED') {
                if (blank($personel->nikc)) {
                    DB::rollBack();
                    return back()->withErrors(['nikc' => 'Pendaftaran tidak dapat disetujui karena NIKC personel belum terisi.']);
                }

                // 1. Aktifkan status hak masuk akun user
                User::where('id', $personel->user_id)->update(['is_active' => true]);

                // 🌟 PERBAIKAN MUTLAK: Ambil NIKC bawaan yang sudah diisi pendaftar saat registrasi
                $nikc = $personel->nikc;

                // 3. Kirim Notifikasi WA, Email, dan SystemNotification
                $msg = "Selamat *{$personel->full_name}*, pendaftaran Anda di Sisfoperskc telah *DISETUJUI*. NIKC Anda: {$nikc}. Silakan login menggunakan NIKC Anda dan lakukan verifikasi OTP.";
                \App\Services\WhatsappService::sendMessage($personel->phone_number, $msg);

                if ($personel->user) {
                    $personel->user->notify(new \App\Notifications\SystemNotification(
                        'Pendaftaran Akun Disetujui',
                        "Selamat {$personel->full_name}, pendaftaran Anda telah DISETUJUI oleh administrator. NIKC Anda: {$nikc}.",
                        'check',
                        route('login')
                    ));
                }
            } else {
                // Jika pendaftaran ditolak oleh administrator
                // 1. Kirim notifikasi WA & Email penolakan terlebih dahulu
                $msg = "Mohon maaf *{$personel->full_name}*, pendaftaran Anda di Sisfoperskc ditolak dengan catatan: " . ($request->admin_notes ?? 'Berkas pendukung tidak valid.');
                \App\Services\WhatsappService::sendMessage($personel->phone_number, $msg);

                if ($personel->user && $personel->user->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($personel->user->email)->send(
                            new \App\Mail\OtpNotificationMail(
                                $personel->full_name,
                                $personel->nikc ?? '-',
                                'REJECTED',
                                'Pendaftaran Ditolak (' . ($request->admin_notes ?? 'Berkas Tidak Valid') . ')',
                                'Nonaktif'
                            )
                        );
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Gagal kirim email penolakan: " . $e->getMessage());
                    }
                }

                // 2. Hapus secara permanen personel, seluruh berkas fisik di disk, tabel relasi, dan akun User (email & username)
                Personel::purgePersonelCompletely($personel);
            }

            DB::commit();
            return redirect()->route('admin.verification.index')->with('success', 'Status pendaftaran personel berhasil diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses validasi berkas: ' . $e->getMessage()]);
        }
    }
}
    /**
     * Unggah / Update Berkas Foto Profil atau KTP Pendaftar oleh Admin
     */
    public function uploadDocument(Request $request, $uuid)
    {
        $personel = Personel::where('uuid', $uuid)->firstOrFail();
        
        $request->validate([
            'type' => 'required|in:photo_profile,ktp_document',
            'file' => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        $type = $request->type;
        $folder = $type === 'photo_profile' ? 'personel/photos' : 'personel/documents';

        try {
            $path = $request->file('file')->store($folder, 'private');
        } catch (\Exception $e) {
            $path = $request->file('file')->store($folder, 'local');
        }

        $personel->update([$type => $path]);

        return back()->with('success', 'Berkas pendaftar berhasil diunggah.');
    }
}