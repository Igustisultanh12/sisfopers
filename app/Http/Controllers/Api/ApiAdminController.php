<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\User;
use App\Models\Broadcast;
use App\Models\BroadcastTarget;
use App\Models\SkepRequest;
use App\Models\SkepData;
use App\Notifications\NewBroadcastNotification;
use App\Jobs\SendWhatsappNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiAdminController extends Controller
{
    /**
     * Get Stats for Admin / Coordinator Dashboard
     */
    public function dashboardStats()
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'personel';

        if (!in_array($role, ['admin', 'kordinator_angkatan', 'kordinator_matra'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized role.'], 403);
        }

        $myProfile = $user->personel;
        $isAngkatan = ($role === 'kordinator_angkatan');
        $isMatra = ($role === 'kordinator_matra');

        // Total Personel
        $query = Personel::query();
        if ($isAngkatan && $myProfile) {
            $query->where('angkatan', $myProfile->angkatan);
        } elseif ($isMatra && $myProfile) {
            $query->where('matra', $myProfile->matra);
        }

        $totalPersonel = $query->count();
        $aktifPersonel = (clone $query)->whereHas('user', function($q) { $q->where('is_active', true); })->count();
        $verifiedPersonel = (clone $query)->where('face_verified', true)->count();

        // Pending SKEP requests (only relevant for admin)
        $pendingSkep = 0;
        if ($role === 'admin') {
            $pendingSkep = SkepRequest::where('status', 'PENDING')->count();
        }

        // Matra distribution stats
        $adCount = (clone $query)->where('matra', 'AD')->count();
        $alCount = (clone $query)->where('matra', 'AL')->count();
        $auCount = (clone $query)->where('matra', 'AU')->count();

        return response()->json([
            'success' => true,
            'role' => $role,
            'stats' => [
                'total_personel' => $totalPersonel,
                'total_aktif' => $aktifPersonel,
                'total_verified' => $verifiedPersonel,
                'pending_skep' => $pendingSkep,
            ],
            'matra_distribution' => [
                'AD' => $adCount,
                'AL' => $alCount,
                'AU' => $auCount,
            ]
        ]);
    }

    /**
     * Get list of pending SKEP Requests (Admin only)
     */
    public function skepRequests()
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $requests = SkepRequest::where('status', 'PENDING')->latest()->get();

        // Format document URL
        $formatted = $requests->map(function ($req) {
            return [
                'id' => $req->id,
                'nikc' => $req->nikc,
                'nama_lengkap' => $req->nama_lengkap,
                'dob' => $req->dob ? $req->dob->toDateString() : null,
                'pangkat' => $req->pangkat,
                'angkatan' => $req->angkatan,
                'matra' => $req->matra,
                'phone_number' => $req->phone_number,
                'ktp_document' => $req->ktp_document ? url($req->ktp_document) : null,
                'created_at' => $req->created_at ? $req->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    /**
     * Verify SKEP Request (Admin only)
     */
    public function verifySkepRequest(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:APPROVED,REJECTED',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $skepRequest = SkepRequest::findOrFail($id);
            
            $skepRequest->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? null,
                'verified_by' => $user->id,
                'verified_at' => now()
            ]);

            if ($validated['status'] === 'APPROVED') {
                SkepData::updateOrCreate(
                    ['nikc' => $skepRequest->nikc],
                    [
                        'nama_lengkap' => $skepRequest->nama_lengkap,
                        'dob' => $skepRequest->dob,
                        'pangkat' => $skepRequest->pangkat,
                        'angkatan' => $skepRequest->angkatan,
                        'matra' => $skepRequest->matra,
                    ]
                );

                $msg = "Halo *{$skepRequest->nama_lengkap}*,\n\nPengajuan verifikasi berkas SKEP Anda dengan NIKC *{$skepRequest->nikc}* telah *DISETUJUI* oleh Admin. Anda sekarang dapat melanjutkan proses pendaftaran di web SISFOPERSKC.\n\nSilakan daftar di link berikut:\n" . route('register');
                \App\Services\WhatsappService::sendMessage($skepRequest->phone_number, $msg);
            } else {
                $catatan = $validated['admin_notes'] ?? 'Berkas tidak sesuai atau kurang jelas.';
                $msg = "Halo *{$skepRequest->nama_lengkap}*,\n\nPengajuan verifikasi berkas SKEP Anda dengan NIKC *{$skepRequest->nikc}* telah *DITOLAK* oleh Admin dengan catatan:\n_\"{$catatan}\"_\n\nSilakan unggah kembali berkas SKEP yang valid di halaman pendaftaran.";
                \App\Services\WhatsappService::sendMessage($skepRequest->phone_number, $msg);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Verifikasi SKEP berhasil diproses.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of Personel (restricted by coordinator scope)
     */
    public function personelList(Request $request)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'personel';

        if (!in_array($role, ['admin', 'kordinator_angkatan', 'kordinator_matra'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $myProfile = $user->personel;
        $isAngkatan = ($role === 'kordinator_angkatan');
        $isMatra = ($role === 'kordinator_matra');

        $query = Personel::with('user');

        if ($isAngkatan && $myProfile) {
            $query->where('angkatan', $myProfile->angkatan);
        } elseif ($isMatra && $myProfile) {
            $query->where('matra', $myProfile->matra);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%')
                  ->orWhere('nikc', 'like', '%' . $search . '%');
            });
        }

        $personels = $query->latest()->get();

        $formatted = $personels->map(function ($p) {
            return [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'full_name' => $p->full_name,
                'nik' => $p->nik,
                'nikc' => $p->nikc,
                'pangkat' => $p->pangkat,
                'matra' => $p->matra,
                'angkatan' => $p->angkatan,
                'phone_number' => $p->phone_number,
                'status_profile' => $p->status_profile,
                'photo_profile' => $p->photo_profile ? url($p->photo_profile) : null,
                'address' => $p->address,
                'province' => $p->province,
                'city' => $p->city,
                'district' => $p->district,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    /**
     * Store New Broadcast (Admin & Coordinators)
     */
    public function storeBroadcast(Request $request)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'personel';

        if (!in_array($role, ['admin', 'kordinator_angkatan', 'kordinator_matra'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

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
            $broadcast = Broadcast::create(array_merge($validated, [
                'uuid' => (string) Str::uuid(),
                'created_by' => $user->id
            ]));

            // Gather target personels based on targets
            $query = Personel::with('user')->where('face_verified', true);
            
            if ($validated['target_type'] === 'MATRA') {
                $query->where('matra', $validated['target_value']);
            } elseif ($validated['target_type'] === 'ANGKATAN') {
                $query->where('angkatan', $validated['target_value']);
            }

            // Security: If coordinator matra/angkatan, force filter to their scope
            $myProfile = $user->personel;
            if ($role === 'kordinator_matra' && $myProfile) {
                $query->where('matra', $myProfile->matra);
            } elseif ($role === 'kordinator_angkatan' && $myProfile) {
                $query->where('angkatan', $myProfile->angkatan);
            }
            
            $targetPersonels = $query->get();

            foreach ($targetPersonels as $personel) {
                BroadcastTarget::create([
                    'broadcast_id' => $broadcast->id,
                    'personel_id'  => $personel->id
                ]);

                if ($personel->user) {
                    $personel->user->notify(new NewBroadcastNotification($broadcast));
                }

                $waMessage = "📢 *PEMBERITAHUAN KEGIATAN BARU: {$broadcast->title}*\n\n"
                    . "Kategori: " . strtoupper($broadcast->category) . "\n"
                    . "Waktu: " . date('d-m-Y', strtotime($broadcast->event_date)) . " | Pukul {$broadcast->event_time} WIB\n"
                    . "Lokasi: {$broadcast->location}\n\n"
                    . "Deskripsi:\n{$broadcast->description}\n\n"
                    . "⚠️ *Batas Waktu Konfirmasi:* " . date('d-m-Y H:i', strtotime($broadcast->deadline)) . " WIB\n\n"
                    . "Silakan login ke platform SISFOPERSKC untuk mengisi lembar kehadiran Anda.";

                \App\Services\WhatsappService::sendMessage($personel->phone_number, $waMessage);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Instruksi broadcast kegiatan berhasil dikirim ke ' . $targetPersonels->count() . ' personel.'
            ]);
        
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim broadcast: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Pending Registrations
     */
    public function getPendingRegistrations(Request $request)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'personel';
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized role.'], 403);
        }

        $query = Personel::whereHas('registration', function ($q) {
            $q->where('status_verification', 'PENDING');
        })->with(['user', 'registration']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%')
                  ->orWhere('nikc', 'like', '%' . $search . '%');
            });
        }

        $pendaftar = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $pendaftar->map(function ($p) {
                return [
                    'id' => $p->id,
                    'uuid' => $p->uuid,
                    'full_name' => $p->full_name,
                    'nik' => $p->nik,
                    'nikc' => $p->nikc,
                    'matra' => $p->matra,
                    'angkatan' => $p->angkatan,
                    'pangkat' => $p->pangkat,
                    'phone_number' => $p->phone_number,
                    'photo_profile' => $p->photo_profile ? url('storage/' . $p->photo_profile) : null,
                    'ktp_document' => $p->ktp_document ? url('storage/' . $p->ktp_document) : null,
                    'registered_at' => $p->created_at->toISOString(),
                ];
            })
        ]);
    }

    /**
     * Verify Registration
     */
    public function verifyRegistration(Request $request, $uuid)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'personel';
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized role.'], 403);
        }

        $request->validate([
            'status' => 'required|in:APPROVED,REJECTED',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $personel = Personel::where('uuid', $uuid)->firstOrFail();
            $registration = \App\Models\Registration::where('personel_id', $personel->id)->firstOrFail();

            $registration->update([
                'status_verification' => $request->status,
                'admin_notes' => $request->admin_notes,
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            if ($request->status === 'APPROVED') {
                if (blank($personel->nikc)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Pendaftaran tidak dapat disetujui karena NIKC personel belum terisi.',
                    ], 422);
                }

                User::where('id', $personel->user_id)->update(['is_active' => true]);
                $nikc = $personel->nikc;
                $msg = "Selamat *{$personel->full_name}*, pendaftaran Anda di SISFOPERSKC telah *DISETUJUI*. NIKC Anda: {$nikc}. Silakan login menggunakan NIKC Anda dan lakukan verifikasi OTP WhatsApp.";
                \App\Services\WhatsappService::sendMessage($personel->phone_number, $msg);
            } else {
                // Jika pendaftaran ditolak oleh administrator
                // 1. Kirim notifikasi WA penolakan terlebih dahulu
                $msg = "Mohon maaf *{$personel->full_name}*, pendaftaran Anda di SISFOPERSKC ditolak dengan catatan: " . ($request->admin_notes ?? 'Berkas pendukung tidak valid.');
                \App\Services\WhatsappService::sendMessage($personel->phone_number, $msg);

                // 2. Hapus secara permanen personel, seluruh berkas fisik di disk, tabel relasi, dan akun User (email & username)
                Personel::purgePersonelCompletely($personel);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Status pendaftaran personel berhasil diverifikasi.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi pendaftaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get System Logs
     */
    public function getSystemLogs(Request $request)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'personel';
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized role.'], 403);
        }

        // Fetch Audit Logs
        $auditLogs = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->leftJoin('personels', 'users.id', '=', 'personels.user_id')
            ->select('audit_logs.*', 'users.username', 'personels.full_name')
            ->latest('audit_logs.created_at')
            ->limit(50)
            ->get();

        // Fetch Login Logs
        $loginLogs = DB::table('login_logs')
            ->join('users', 'login_logs.user_id', '=', 'users.id')
            ->leftJoin('personels', 'users.id', '=', 'personels.user_id')
            ->select('login_logs.*', 'users.username', 'personels.full_name')
            ->latest('login_logs.login_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'audit_logs' => $auditLogs,
            'login_logs' => $loginLogs
        ]);
    }

    /**
     * Get Settings
     */
    public function getSettings()
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'personel';
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized role.'], 403);
        }

        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }

    /**
     * Update Settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'personel';
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized role.'], 403);
        }

        $rules = $request->validate([
            'app_name' => 'required|string|max:50',
            'wa_host' => 'required|string',
            'wa_port' => 'required|numeric',
            'wa_api_key' => 'nullable|string',
            'wa_session' => 'required|string'
        ]);

        foreach ($rules as $key => $value) {
            if ($key === 'wa_api_key' && empty($value)) {
                continue; // Pertahankan token lama jika input dikosongkan
            }
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi parameter sistem berhasil disimpan.'
        ]);
    }

    /**
     * Test WA Connection
     */
    public function testWaConnection()
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'personel';
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized role.'], 403);
        }

        $settingController = new \App\Http\Controllers\Admin\SettingController();
        return $settingController->testWaConnection();
    }

    /**
     * Get Report Summaries
     */
    public function getReportSummaries()
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'personel';
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized role.'], 403);
        }

        $total = Personel::count();
        $asn = Personel::where('is_asn', true)->count();
        $nonAsn = $total - $asn;
        $ad = Personel::where('matra', 'AD')->count();
        $al = Personel::where('matra', 'AL')->count();
        $au = Personel::where('matra', 'AU')->count();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_personel' => $total,
                'total_asn' => $asn,
                'total_non_asn' => $nonAsn,
                'tni_ad' => $ad,
                'tni_al' => $al,
                'tni_au' => $au,
            ],
            'export_urls' => [
                'personel_excel' => url('/admin/reports/personel/excel'),
                'personel_pdf' => url('/admin/reports/personel/pdf'),
            ]
        ]);
    }
}
