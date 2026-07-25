<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\PengkinianData;
use App\Models\Personel;
use App\Models\User;
use App\Mail\SystemNotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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
                    . "Nama: " . $personel->full_name . "\n"
                    . "NIKC: " . $personel->nikc . "\n"
                    . "Kategori: " . $item->jenis_pengkinian . "\n"
                    . "Silakan login ke dashboard Admin SISFOPERS untuk memproses pengajuan ini.";
                \App\Services\WhatsappService::sendMessage($adminUser->personel->phone_number, $msgAdmin);
            }

            if ($adminUser->email) {
                try {
                    Mail::to($adminUser->email)->send(new SystemNotificationMail(
                        'Pengajuan Pengkinian Data Baru',
                        "Terdapat pengajuan pengkinian data baru (" . $item->jenis_pengkinian . ") dari personel <strong>" . $personel->full_name . "</strong> (NIKC: " . $personel->nikc . ").",
                        $adminUser,
                        route('admin.pengkinian-data.index')
                    ));
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim email pengkinian data ke admin " . $adminUser->email . ": " . $e->getMessage());
                }
            }
        }

        return back()->with('success', 'Pengajuan pengkinian data berhasil dikirim. Harap tunggu verifikasi administrator.');
    }

    public function searchPersonel(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasRole('admin'), 403);

        $q = trim($request->input('query', ''));
        if (!$q) {
            return response()->json([]);
        }

        $personels = Personel::where('full_name', 'like', "%{$q}%")
            ->orWhere('nikc', 'like', "%{$q}%")
            ->orWhere('nik', 'like', "%{$q}%")
            ->limit(15)
            ->get(['id', 'full_name', 'nikc', 'nik', 'pangkat', 'matra', 'status_keaktifan']);

        return response()->json($personels);
    }

    public function adminStore(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasRole('admin'), 403);

        $request->validate([
            'personel_id'      => 'required|exists:personels,id',
            'jenis_pengkinian' => 'required|in:MENINGGAL,TNI_AD,TNI_AL,TNI_AU,POLRI',
            'document'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'nrp'              => 'nullable|string|max:50',
            'tmt_pengangkatan' => 'nullable|date',
            'tmt_masuk_satuan' => 'nullable|date',
            'satuan'           => 'nullable|string|max:150',
            'jabatan'          => 'nullable|string|max:150',
            'catatan'          => 'nullable|string|max:1000',
        ]);

        $personel = Personel::with('user')->findOrFail($request->personel_id);

        $filePath = 'personel/pengkinian_data/admin_entry.pdf';
        if ($request->hasFile('document')) {
            $filePath = $request->file('document')->store('personel/pengkinian_data', 'private');
        }

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
            'status'           => 'APPROVED',
            'verified_by'      => $user->id,
            'verified_at'      => now(),
        ]);

        $personel->update([
            'status_keaktifan' => $request->jenis_pengkinian
        ]);

        if ($request->jenis_pengkinian === 'MENINGGAL' && $personel->user) {
            $personel->user->update([
                'is_active' => false
            ]);
        }

        if ($personel->phone_number) {
            $msg = "Halo *" . $personel->full_name . "*, administrator telah meng-update status pengkinian data Anda menjadi: *" . $request->jenis_pengkinian . "*.";
            if ($request->jenis_pengkinian === 'MENINGGAL') {
                $msg .= "\n\nCatatan: Akun personel terkait telah dinonaktifkan secara otomatis oleh sistem.";
            }
            \App\Services\WhatsappService::sendMessage($personel->phone_number, $msg);
        }

        return back()->with('success', 'Data pengkinian personel berhasil ditambahkan dan langsung terverifikasi.');
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

            if ($item->jenis_pengkinian === 'MENINGGAL' && $item->personel->user) {
                $item->personel->user->update([
                    'is_active' => false
                ]);
            }

            if ($item->personel->user) {
                $item->personel->user->notify(new \App\Notifications\SystemNotification(
                    'Pengkinian Data Disetujui',
                    "Pengajuan pengkinian data (" . $item->jenis_pengkinian . ") Anda telah DISETUJUI oleh Admin." . ($item->jenis_pengkinian === 'MENINGGAL' ? " Akun Anda telah dinonaktifkan." : ""),
                    'pengkinian_data',
                    route('personel.pengkinian-data.index')
                ));
            }

            if ($item->personel->phone_number) {
                $msg = "Halo *" . $item->personel->full_name . "*, pengajuan pengkinian data (" . $item->jenis_pengkinian . ") Anda telah *DISETUJUI* oleh Admin. Terima kasih.";
                if ($item->jenis_pengkinian === 'MENINGGAL') {
                    $msg .= "\n\nCatatan: Akun personel terkait telah dinonaktifkan secara otomatis oleh sistem.";
                }
                \App\Services\WhatsappService::sendMessage($item->personel->phone_number, $msg);
            }

            if ($item->personel->user && $item->personel->user->email) {
                try {
                    $mailText = "Pengajuan pengkinian data (" . $item->jenis_pengkinian . ") Anda telah <strong>DISETUJUI</strong> oleh Admin.";
                    if ($item->jenis_pengkinian === 'MENINGGAL') {
                        $mailText .= "<br/><br/><strong>Catatan:</strong> Akun personel terkait telah dinonaktifkan secara otomatis dari sistem SISFOPERS.";
                    }
                    Mail::to($item->personel->user->email)->send(new SystemNotificationMail(
                        'Pengkinian Data Disetujui',
                        $mailText,
                        $item->personel,
                        route('personel.pengkinian-data.index')
                    ));
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim email disetujui pengkinian data ke " . $item->personel->user->email . ": " . $e->getMessage());
                }
            }
        }

        return back()->with('success', 'Pengkinian data berhasil disetujui.' . ($item->jenis_pengkinian === 'MENINGGAL' ? ' Akun personel otomatis dinonaktifkan.' : ''));
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
                    'Pengajuan pengkinian data (' . $item->jenis_pengkinian . ') Anda DITOLAK dengan alasan: "' . $request->reason . '".',
                    'pengkinian_data',
                    route('personel.pengkinian-data.index')
                ));
            }

            if ($item->personel->phone_number) {
                $msg = 'Halo *' . $item->personel->full_name . '*, pengajuan pengkinian data (' . $item->jenis_pengkinian . ') Anda *DITOLAK* dengan alasan: "' . $request->reason . '". Silakan ajukan ulang dengan berkas yang sesuai.';
                \App\Services\WhatsappService::sendMessage($item->personel->phone_number, $msg);
            }

            if ($item->personel->user && $item->personel->user->email) {
                try {
                    Mail::to($item->personel->user->email)->send(new SystemNotificationMail(
                        'Pengkinian Data Ditolak',
                        'Pengajuan pengkinian data (' . $item->jenis_pengkinian . ') Anda <strong>DITOLAK</strong> oleh Admin dengan alasan:<br/><blockquote style="color:#dc2626; margin:10px 0;">"' . $request->reason . '"</blockquote>Silakan ajukan ulang dengan berkas yang sesuai.',
                        $item->personel,
                        route('personel.pengkinian-data.index')
                    ));
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim email ditolak pengkinian data ke " . $item->personel->user->email . ": " . $e->getMessage());
                }
            }
        }

        return back()->with('success', 'Pengkinian data berhasil ditolak.');
    }
}
