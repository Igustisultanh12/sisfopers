<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Personel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TicketAdminController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Ticket::with(['personel.user', 'verifier', 'logs.user.personel', 'logs.user.role']);

        // Scope otomatis untuk Koordinator Matra & Angkatan
        if ($user->hasRole('kordinator_matra') && $user->matra) {
            $query->whereHas('personel', function ($q) use ($user) {
                $q->where('matra', $user->matra);
            });
        } elseif ($user->hasRole('kordinator_angkatan')) {
            $query->whereHas('personel', function ($q) use ($user) {
                if ($user->matra) {
                    $q->where('matra', $user->matra);
                }
                if ($user->angkatan) {
                    $q->where('angkatan', $user->angkatan);
                }
            });
        }

        // Filter Pencarian (No Tiket, Nama, NIKC)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhereHas('personel', function ($sub) use ($search) {
                      $sub->where('full_name', 'like', "%{$search}%")
                          ->orWhere('nikc', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Ticket/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $user = $request->user();
        $ticket = Ticket::with('personel.user')->findOrFail($id);

        // Authorization Scope Check
        if ($user->hasRole('kordinator_matra') && $user->matra && $ticket->personel->matra !== $user->matra) {
            abort(403, 'Anda tidak berwenang memproses tiket di luar matra Anda.');
        }
        if ($user->hasRole('kordinator_angkatan')) {
            if ($user->matra && $ticket->personel->matra !== $user->matra) {
                abort(403, 'Anda tidak berwenang memproses tiket di luar matra Anda.');
            }
            if ($user->angkatan && $ticket->personel->angkatan != $user->angkatan) {
                abort(403, 'Anda tidak berwenang memproses tiket di luar angkatan Anda.');
            }
        }

        $request->validate([
            'status'           => 'required|in:DIPROSES,DISETUJUI,DITOLAK,SELESAI',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $newStatus = $request->status;
        $personel  = $ticket->personel;

        // FITUR KHUSUS: Jika kategori UBAH_FOTO dan disetujui, update foto profil personel & hapus foto lama!
        if ($newStatus === 'DISETUJUI' && $ticket->category === 'UBAH_FOTO' && $ticket->attachment_path) {
            if ($personel) {
                $oldPhoto = $personel->photo_profile;
                if ($oldPhoto && Storage::disk('private')->exists($oldPhoto)) {
                    try {
                        Storage::disk('private')->delete($oldPhoto);
                    } catch (\Exception $e) {}
                }
                $personel->update([
                    'photo_profile' => $ticket->attachment_path
                ]);
            }
        }

        // FITUR OTOMATIS: Jika pengajuan DITOLAK, hapus berkas lampiran yang diunggah dari server disk agar tidak memenuhi memori!
        if ($newStatus === 'DITOLAK' && $ticket->attachment_path) {
            if (Storage::disk('private')->exists($ticket->attachment_path)) {
                try {
                    Storage::disk('private')->delete($ticket->attachment_path);
                } catch (\Exception $e) {}
            }
            $ticket->attachment_path = null;
        }

        $ticket->update([
            'status'           => $newStatus,
            'attachment_path'  => $ticket->attachment_path,
            'rejection_reason' => $newStatus === 'DITOLAK' ? $request->rejection_reason : null,
            'verified_by'      => $user->id,
            'verified_at'      => now(),
        ]);

        // Rekam Audit Log Riwayat Proses Tiket
        $note = match ($newStatus) {
            'DITOLAK'   => "Pengajuan ditolak. Alasan: " . ($request->rejection_reason ?? '-'),
            'DISETUJUI' => "Pengajuan disetujui oleh verifikator.",
            'SELESAI'   => "Proses tiket pengaduan selesai.",
            default     => "Status tiket diperbarui menjadi {$newStatus}.",
        };

        \App\Models\TicketLog::create([
            'ticket_id' => $ticket->id,
            'status'    => $newStatus,
            'note'      => $note,
            'user_id'   => $user->id,
        ]);

        // Kirim Notifikasi Sistem & WA ke Personel Pembuat Tiket
        try {
            $statusLabels = [
                'DIPROSES'  => 'Sedang Diproses',
                'DISETUJUI' => 'Disetujui',
                'DITOLAK'   => 'Ditolak',
                'SELESAI'   => 'Selesai / Tuntas',
            ];
            $statusStr = $statusLabels[$newStatus] ?? $newStatus;

            if ($personel && $personel->user) {
                $personel->user->notify(new \App\Notifications\SystemNotification(
                    'Pembaruan Status Tiket Pengaduan',
                    "Tiket pengaduan Anda ({$ticket->ticket_number}) telah diperbarui menjadi: {$statusStr}." . ($newStatus === 'DITOLAK' ? " Alasan: {$request->rejection_reason}" : ""),
                    'ticket',
                    route('personel.tickets.index')
                ));
            }

            if ($personel && $personel->phone_number) {
                $msg = "*STATUS TIKET PENGADUAN PERBARUAN*\n\n"
                    . "Nomor Tiket: *{$ticket->ticket_number}*\n"
                    . "Status Baru: *{$statusStr}*\n";
                if ($newStatus === 'DITOLAK' && $request->rejection_reason) {
                    $msg .= "Alasan Penolakan: {$request->rejection_reason}\n";
                }
                $msg .= "\nTerima kasih.";
                \App\Services\WhatsappService::sendMessage($personel->phone_number, $msg);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal notifikasi status tiket: " . $e->getMessage());
        }

        return back()->with('success', "Status tiket {$ticket->ticket_number} berhasil diperbarui menjadi {$newStatus}.");
    }

    public function destroy(int $id)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('admin'), 403);

        $ticket = Ticket::findOrFail($id);
        if ($ticket->attachment_path && Storage::disk('private')->exists($ticket->attachment_path)) {
            try {
                Storage::disk('private')->delete($ticket->attachment_path);
            } catch (\Exception $e) {}
        }
        $ticket->delete();

        return back()->with('success', 'Data tiket berhasil dihapus.');
    }
}
