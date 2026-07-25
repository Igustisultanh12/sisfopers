<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\PengkinianData;
use App\Models\Personel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PengkinianDataController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $personel = $user->personel;

        if (!$personel) {
            $redirectRoute = $user->hasRole('admin') ? 'admin.dashboard' : 'personel.dashboard';
            return redirect()->route($redirectRoute)->with('error', 'Profil personel Anda belum terkonfigurasi.');
        }

        $items = PengkinianData::where('personel_id', $personel->id)
            ->with('verifier')
            ->latest()
            ->get();

        return Inertia::render('Personel/PengkinianData/Index', [
            'personel' => $personel->load('user'),
            'items'    => $items,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $personel = $user->personel;

        if (!$personel) {
            return back()->withErrors(['error' => 'Data personel tidak ditemukan.']);
        }

        $request->validate([
            'jenis_pengkinian' => 'required|in:MENINGGAL,TNI_AD,TNI_AL,TNI_AU,POLRI',
            'document'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'nrp'              => 'nullable|string|max:50',
            'tmt_pengangkatan' => 'nullable|date',
            'tmt_masuk_satuan' => 'nullable|date',
            'satuan'           => 'nullable|string|max:150',
            'jabatan'          => 'nullable|string|max:150',
            'catatan'          => 'nullable|string|max:1000',
        ]);

        $filePath = $request->file('document')->store('personel/pengkinian_data', 'private');

        $item = PengkinianData::create([
            'uuid'             => Str::uuid(),
            'personel_id'      => $personel->id,
            'jenis_pengkinian' => $request->jenis_pengkinian,
            'document_path'    => $filePath,
            'nrp'              => $request->nrp,
            'tmt_pengangkatan' => $request->tmt_pengangkatan,
            'tmt_masuk_satuan' => $request->tmt_masuk_satuan,
            'satuan'           => $request->satuan,
            'jabatan'          => $request->jabatan,
            'catatan'          => $request->catatan,
            'status'           => 'PENDING',
        ]);

        $admins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->with('personel')->get();
        foreach ($admins as $adminUser) {
            if ($adminUser->personel && $adminUser->personel->phone_number) {
                $msgAdmin = "🔔 *PENGAJUAN PENGKINIAN DATA BARU*\n\n"
                    . "Nama: {$personel->full_name}\n"
                    . "NIKC: {$personel->nikc}\n"
                    . "Kategori: {$item->jenis_pengkinian}\n"
                    . "Silakan login ke dashboard Admin SISFOPERS untuk memproses pengajuan ini.";
                \App\Services\WhatsappService::sendMessage($adminUser->personel->phone_number, $msgAdmin);
            }
        }

        return back()->with('success', 'Pengajuan pengkinian data berhasil dikirim. Harap tunggu verifikasi administrator.');
    }

    public function adminIndex(Request $request)
    {
        $query = PengkinianData::with(['personel.user', 'verifier']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('personel', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nikc', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_pengkinian', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/VerifikasiPengkinian/Index', [
            'items'   => $items,
            'filters' => $request->only(['search', 'jenis', 'status']),
        ]);
    }

    public function verify(Request $request, int $id)
    {
        $user = $request->user();
        abort_unless($user->hasRole('admin'), 403);

        $item = PengkinianData::with('personel.user')->findOrFail($id);

        $item->update([
            'status'      => 'APPROVED',
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);

        if ($item->personel) {
            $item->personel->update([
                'status_keaktifan' => $item->jenis_pengkinian
            ]);

            if ($item->personel->user) {
                $item->personel->user->notify(new \App\Notifications\SystemNotification(
                    'Pengkinian Data Disetujui',
                    "Pengajuan pengkinian data ({$item->jenis_pengkinian}) Anda telah DISETUJUI oleh Admin.",
                    'pengkinian_data',
                    route('personel.pengkinian-data.index')
                ));
            }

            if ($item->personel->phone_number) {
                $msg = "Halo *{$item->personel->full_name}*, pengajuan pengkinian data ({$item->jenis_pengkinian}) Anda telah *DISETUJUI* oleh Admin. Terima kasih.";
                \App\Services\WhatsappService::sendMessage($item->personel->phone_number, $msg);
            }
        }

        return back()->with('success', 'Pengkinian data berhasil disetujui.');
    }

    public function reject(Request $request, int $id)
    {
        $user = $request->user();
        abort_unless($user->hasRole('admin'), 403);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $item = PengkinianData::with('personel.user')->findOrFail($id);

        $item->update([
            'status'           => 'REJECTED',
            'rejection_reason' => $request->reason,
            'verified_by'       => $user->id,
            'verified_at'       => now(),
        ]);

        if ($item->personel) {
            if ($item->personel->user) {
                $item->personel->user->notify(new \App\Notifications\SystemNotification(
                    'Pengkinian Data Ditolak',
                    "Pengajuan pengkinian data ({$item->jenis_pengkinian}) Anda DITOLAK dengan alasan: "{$request->reason}".",
                    'pengkinian_data',
                    route('personel.pengkinian-data.index')
                ));
            }

            if ($item->personel->phone_number) {
                $msg = "Halo *{$item->personel->full_name}*, pengajuan pengkinian data ({$item->jenis_pengkinian}) Anda *DITOLAK* dengan alasan: "{$request->reason}". Silakan ajukan ulang dengan berkas yang sesuai.";
                \App\Services\WhatsappService::sendMessage($item->personel->phone_number, $msg);
            }
        }

        return back()->with('success', 'Pengkinian data berhasil ditolak.');
    }
}
