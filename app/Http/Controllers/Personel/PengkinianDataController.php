<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\PengkinianData;
use App\Models\Personel;
use App\Models\SkepData;
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

        $results = [];
        $addedIds = [];

        try {
            // 1. Cari dari Database Master Personel
            $personels = Personel::where('full_name', 'like', "%{$q}%")
                ->orWhere('nikc', 'like', "%{$q}%")
                ->orWhere('nik', 'like', "%{$q}%")
                ->limit(10)
                ->get(['id', 'full_name', 'nikc', 'nik', 'pangkat', 'matra', 'angkatan', 'phone_number', 'province', 'city', 'subdistrict', 'status_keaktifan']);

            foreach ($personels as $p) {
                $addedIds[] = $p->id;
                $results[] = [
                    'id'               => $p->id,
                    'full_name'        => $p->full_name,
                    'nikc'             => $p->nikc ?: $p->nik,
                    'nik'              => $p->nik,
                    'pangkat'          => $p->pangkat,
                    'matra'            => $p->matra,
                    'angkatan'         => $p->angkatan,
                    'phone_number'     => $p->phone_number,
                    'province'         => $p->province,
                    'city'             => $p->city,
                    'subdistrict'       => $p->subdistrict,
                    'status_keaktifan' => $p->status_keaktifan ?: 'AKTIF',
                    'source'           => 'MASTER_PERSONEL',
                ];
            }

            // 2. Cari dari Database Master SKEP
            $skepItems = SkepData::where('nama_lengkap', 'like', "%{$q}%")
                ->orWhere('nikc', 'like', "%{$q}%")
                ->limit(10)
                ->get();

            foreach ($skepItems as $sk) {
                $existing = Personel::where('nikc', $sk->nikc)->first();
                if ($existing && in_array($existing->id, $addedIds)) {
                    continue;
                }

                if ($existing) {
                    $addedIds[] = $existing->id;
                    $results[] = [
                        'id'               => $existing->id,
                        'full_name'        => $existing->full_name,
                        'nikc'             => $existing->nikc ?: $existing->nik,
                        'nik'              => $existing->nik,
                        'pangkat'          => $existing->pangkat,
                        'matra'            => $existing->matra,
                        'angkatan'         => $existing->angkatan,
                        'phone_number'     => $existing->phone_number,
                        'province'         => $existing->province,
                        'city'             => $existing->city,
                        'subdistrict'       => $existing->subdistrict,
                        'status_keaktifan' => $existing->status_keaktifan ?: 'AKTIF',
                        'source'           => 'MASTER_PERSONEL',
                    ];
                } else {
                    $results[] = [
                        'id'               => null,
                        'full_name'        => $sk->nama_lengkap,
                        'nikc'             => $sk->nikc,
                        'nik'              => null,
                        'pangkat'          => $sk->pangkat,
                        'matra'            => $sk->matra,
                        'angkatan'         => $sk->angkatan,
                        'phone_number'     => null,
                        'province'         => null,
                        'city'             => null,
                        'subdistrict'       => null,
                        'status_keaktifan' => 'DATA SKEP',
                        'source'           => 'SKEP_DATA',
                        'dob'              => $sk->dob?->format('Y-m-d'),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("searchPersonel error: " . $e->getMessage());
        }

        return response()->json($results);
    }

    public function adminStore(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasRole('admin'), 403);

        $request->validate([
            'personel_id'      => 'nullable|exists:personels,id',
            'nikc'             => 'required|string',
            'full_name'        => 'required|string',
            'pangkat'          => 'nullable|string',
            'matra'            => 'nullable|string',
            'angkatan'         => 'nullable|string',
            'phone_number'     => 'nullable|string',
            'province'         => 'nullable|string',
            'city'             => 'nullable|string',
            'subdistrict'       => 'nullable|string',
            'jenis_pengkinian' => 'required|in:MENINGGAL,TNI_AD,TNI_AL,TNI_AU,POLRI',
            'document'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'nrp'              => 'nullable|string|max:50',
            'tmt_pengangkatan' => 'nullable|date',
            'tmt_masuk_satuan' => 'nullable|date',
            'satuan'           => 'nullable|string|max:150',
            'jabatan'          => 'nullable|string|max:150',
            'catatan'          => 'nullable|string|max:1000',
        ]);

        $personel = null;
        if ($request->filled('personel_id')) {
            $personel = Personel::with('user')->find($request->personel_id);
        }

        if (!$personel) {
            $personel = Personel::where('nikc', $request->nikc)->orWhere('nik', $request->nikc)->first();
        }

        if (!$personel) {
            $skep = SkepData::where('nikc', $request->nikc)->first();
            $personel = Personel::create([
                'uuid'                => Str::uuid(),
                'full_name'           => $request->full_name,
                'nikc'                => $request->nikc,
                'nik'                 => Str::random(16),
                'dob'                 => $skep ? ($skep->dob?->format('Y-m-d') ?: '1990-01-01') : '1990-01-01',
                'pangkat'             => $request->pangkat ?: ($skep ? $skep->pangkat : 'PRADA'),
                'matra'               => $request->matra ?: ($skep ? $skep->matra : 'AD'),
                'angkatan'            => $request->angkatan ?: ($skep ? $skep->angkatan : '2024'),
                'phone_number'        => $request->phone_number,
                'province'            => $request->province,
                'city'                => $request->city,
                'subdistrict'          => $request->subdistrict,
                'status_keaktifan'    => $request->jenis_pengkinian,
                'status_verification' => 'APPROVED',
            ]);
        } else {
            // Update fields jika sebelumnya kosong dan kini diisi oleh admin
            $updateData = [];
            if (!$personel->pangkat && $request->filled('pangkat')) {
                $updateData['pangkat'] = $request->pangkat;
            }
            if (!$personel->matra && $request->filled('matra')) {
                $updateData['matra'] = $request->matra;
            }
            if (!$personel->angkatan && $request->filled('angkatan')) {
                $updateData['angkatan'] = $request->angkatan;
            }
            if (!$personel->phone_number && $request->filled('phone_number')) {
                $updateData['phone_number'] = $request->phone_number;
            }
            if (!$personel->province && $request->filled('province')) {
                $updateData['province'] = $request->province;
            }
            if (!$personel->city && $request->filled('city')) {
                $updateData['city'] = $request->city;
            }
            if (!$personel->subdistrict && $request->filled('subdistrict')) {
                $updateData['subdistrict'] = $request->subdistrict;
            }
            if (!empty($updateData)) {
                $personel->update($updateData);
            }
        }

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
