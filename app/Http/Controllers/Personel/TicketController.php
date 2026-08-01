<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $personel = $user->getPersonelOrAutoCreate();

        if (!$personel) {
            return redirect()->route('personel.dashboard')->with('error', 'Profil personel Anda belum terkonfigurasi.');
        }

        $tickets = Ticket::where('personel_id', $personel->id)
            ->with('verifier')
            ->latest()
            ->get();

        return Inertia::render('Personel/Ticket/Index', [
            'personel' => $personel,
            'tickets'  => $tickets,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $personel = $user->getPersonelOrAutoCreate();

        if (!$personel) {
            return back()->withErrors(['error' => 'Data personel tidak ditemukan.']);
        }

        $category = $request->input('category');

        $rules = [
            'category'    => 'required|in:UBAH_FOTO,UBAH_DATA,CETAK_KTA',
            'description' => 'nullable|string|max:2000',
            'attachment'  => 'nullable|file|max:5120',
        ];

        if ($category === 'UBAH_FOTO') {
            $rules['attachment'] = 'required|image|mimes:jpg,jpeg,png|max:2048';
        } elseif ($category === 'UBAH_DATA') {
            $rules['description'] = 'required|string|min:5|max:2000';
            $rules['attachment']  = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        } elseif ($category === 'CETAK_KTA') {
            $rules['description'] = 'required|string|min:5|max:2000';
            $rules['attachment']  = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        $validated = $request->validate($rules);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $folder = match ($category) {
                'UBAH_FOTO' => 'tickets/photos',
                'UBAH_DATA' => 'tickets/data_changes',
                'CETAK_KTA' => 'tickets/kta_docs',
                default     => 'tickets/attachments',
            };
            $attachmentPath = $request->file('attachment')->store($folder, 'private');
        }

        $ticket = Ticket::create([
            'personel_id'     => $personel->id,
            'category'        => $validated['category'],
            'description'     => $validated['description'] ?? null,
            'attachment_path' => $attachmentPath,
            'status'          => 'DIPROSES',
        ]);

        // Kirim Notifikasi WA ke Koordinator & Admin terkait
        try {
            $admins = User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'kordinator_matra', 'kordinator_angkatan']))
                ->with('personel')
                ->get();

            $categoryLabels = [
                'UBAH_FOTO' => 'Pengajuan Ubah Pasfoto',
                'UBAH_DATA' => 'Pengajuan Perubahan Biodata',
                'CETAK_KTA' => 'Pengajuan Cetak Ulang KTA',
            ];

            $categoryName = $categoryLabels[$ticket->category] ?? $ticket->category;

            foreach ($admins as $adminUser) {
                if ($adminUser->personel && $adminUser->personel->phone_number) {
                    $msg = "*OPEN TIKET PENGADUAN BARU*\n\n"
                        . "Nomor Tiket: *{$ticket->ticket_number}*\n"
                        . "Pendaftar: {$personel->full_name} ({$personel->nikc})\n"
                        . "Kategori: {$categoryName}\n"
                        . "Matra/Angkatan: TNI {$personel->matra} / {$personel->angkatan}\n\n"
                        . "Silakan login ke SISFOPERS untuk memproses tiket ini.";
                    \App\Services\WhatsappService::sendMessage($adminUser->personel->phone_number, $msg);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal notifikasi WA Open Tiket: " . $e->getMessage());
        }

        return back()->with('success', "Tiket pengaduan berhasil dibuat dengan nomor resmi: {$ticket->ticket_number}. Harap tunggu proses verifikasi.");
    }
}
