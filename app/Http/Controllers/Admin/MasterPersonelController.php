<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\User;
use App\Models\Role;
use App\Models\Registration;
use App\Models\MasterKepangkatan;
use App\Models\MasterProvinsi;
use App\Models\SkepData;
use App\Rules\NikcFormatRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MasterPersonelController extends Controller
{
    /**
     * Menampilkan Daftar Kekuatan Master Personel (Eager Loading Komplit)
     */
    public function index(Request $request)
    {
        // Menyertakan relasi penugasan kegiatan & riwayat respon operasional secara taktis
        $query = Personel::with([
            'user', 
            'sinyalmen', 
            'registration', 
            'broadcastResponses.broadcast',
            'jobHistories',
            'riwayatPendidikan'
        ]);

        // Search Handlers
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nikc', 'like', "%{$search}%");
            });
        }

        // Filtering Handlers
        if ($request->filled('matra')) {
            $query->where('matra', $request->matra);
        }
        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        // Filter out unverified registrations
        $query->where(function ($q) {
            $q->whereDoesntHave('registration')
              ->orWhereHas('registration', function ($regQuery) {
                  $regQuery->where('status_verification', 'APPROVED');
              });
        });

        $query->leftJoin('master_kepangkatan', function ($join) {
            $join->on(DB::raw('LOWER(SUBSTRING_INDEX(personels.pangkat, " ", 1))'), '=', DB::raw('LOWER(master_kepangkatan.nama)'));
        })
        ->select('personels.*')
        ->orderByRaw('COALESCE(master_kepangkatan.urutan, 0) DESC');

        $personels = $query->paginate(10)->withQueryString();

        // Pemicu otomatisasi data dikmil militer untuk personel di halaman aktif ini
        foreach ($personels->items() as $p) {
            $p->ensureKomcadEducationExists();
        }

        return Inertia::render('Admin/Personel/Index', [
            'personels' => $personels,
            'filters' => $request->only(['search', 'matra', 'angkatan'])
        ]);
    }

    /**
     * Pencarian Data SKEP Berdasarkan NIKC (Tarik otomatis Nama, Tgl Lahir, Matra, Pangkat, Angkatan)
     */
    public function lookupSkep(Request $request)
    {
        $nikcRaw = $request->query('nikc');
        if (empty($nikcRaw)) {
            return response()->json(['found' => false, 'message' => 'NIKC tidak boleh kosong.']);
        }

        $nikc = SkepData::reconstructNikc($nikcRaw);
        $skep = SkepData::where('nikc', $nikc)
            ->orWhere('nikc', $nikcRaw)
            ->first();

        if ($skep) {
            $alreadyHasAccount = Personel::where('nikc', $nikc)->orWhere('nikc', $nikcRaw)->exists();
            return response()->json([
                'found' => true,
                'data'  => [
                    'nama_lengkap' => $skep->nama_lengkap,
                    'dob'          => $skep->dob ? $skep->dob->format('Y-m-d') : null,
                    'matra'        => strtoupper($skep->matra ?? 'AD'),
                    'angkatan'     => (string) ($skep->angkatan ?? date('Y')),
                    'pangkat'      => $skep->pangkat,
                ],
                'already_has_account' => $alreadyHasAccount,
                'message' => $alreadyHasAccount 
                    ? '⚠️ Data SKEP ditemukan, namun NIKC ini sudah memiliki akun di sistem.' 
                    : '✅ Data SKEP ditemukan! Nama, Tanggal Lahir, Matra, Pangkat, dan Angkatan telah terisi otomatis.'
            ]);
        }

        return response()->json([
            'found'   => false,
            'message' => 'ℹ️ Data NIKC belum terdaftar di SKEP. Silakan input data personel secara manual.'
        ]);
    }

    /**
     * Cetak PDF Informasi Kredensial Akun Personel
     */
    public function printAccountPdf($uuid, Request $request)
    {
        $personel = Personel::where('uuid', $uuid)->with('user')->firstOrFail();
        $password = $request->query('pwd');
        if (empty($password)) {
            $password = $personel->nik;
        }

        $currentUser     = auth()->user();
        $currentPersonel = $currentUser?->personel;
        $signerName      = $currentPersonel?->full_name ?? 'Administrator Utama';
        $signerPangkat   = $currentPersonel?->pangkat ?? 'Administrator Sistem';

        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $nomorSurat = 'REG/' . str_pad($personel->id, 3, '0', STR_PAD_LEFT) . '/PERS/' . $romans[now()->month] . '/' . now()->year;

        // Create Document Verification Record
        $docVerif = \App\Models\DocumentVerification::createRecord(
            'INFORMASI_AKUN',
            'SURAT INFORMASI KREDENSIAL AKUN PERSONEL',
            $personel->full_name,
            $personel->nikc ?: $personel->nik,
            $signerName,
            $signerPangkat,
            ['nomor_surat' => $nomorSurat, 'matra' => $personel->matra, 'angkatan' => $personel->angkatan]
        );

        $verifyUrl = route('public.verify-doc', $docVerif->verify_code);
        $qrCodeBase64 = \App\Services\QrCodeService::generateBase64($verifyUrl);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.account_info_pdf', [
            'personel'      => $personel,
            'password'      => $password,
            'nomorSurat'    => $nomorSurat,
            'generatedAt'   => now()->translatedFormat('d F Y H:i') . ' WIB',
            'signerName'    => $signerName,
            'signerPangkat' => $signerPangkat,
            'verifyCode'    => $docVerif->verify_code,
            'verifyUrl'     => $verifyUrl,
            'qrCodeBase64'  => $qrCodeBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('INFORMASI_AKUN_' . str_replace(' ', '_', $personel->full_name) . '_' . ($personel->nikc ?: $personel->nik) . '.pdf');
    }

    /**
     * Lembar Formulir Penambahan Anggota Baru
     */
    public function create()
    {
        return Inertia::render('Admin/Personel/Create', [
            'pangkatOptions' => MasterKepangkatan::where('is_active', true)
                ->orderBy('urutan')
                ->get(['nama', 'kelompok_nikc', 'klaim_langsung']),
            'provinceOptions' => MasterProvinsi::where('is_active', true)
                ->orderBy('kode_latsarmil')
                ->get(['nama', 'kode_latsarmil']),
        ]);
    }

    /**
     * Memproses Penyimpanan Data Personel Baru Langsung Ke Sistem
     */
    public function store(Request $request)
    {
        if ($request->has('nikc')) {
            $request->merge(['nikc' => SkepData::reconstructNikc($request->nikc)]);
        }

        if ($request->filled('pangkat')) {
            $request->merge([
                'pangkat' => MasterKepangkatan::canonicalName($request->input('pangkat')),
            ]);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'nik' => 'required|digits:16|unique:personels,nik',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'matra' => 'required|in:AD,AL,AU',
            'angkatan' => 'required|digits:4',
            'pangkat' => ['required', Rule::exists('master_kepangkatan', 'nama')->where('is_active', true)],
            'dob' => 'required|date',
            'province' => ['required', 'string', 'max:255', Rule::exists('master_provinsi', 'nama')->where('is_active', true)],
            'nikc' => ['nullable', 'digits:17', 'numeric', new NikcFormatRule($request->input('province'), $request->input('dob'), $request->input('pangkat')), 'unique:personels,nikc'],
            'password' => 'required|string|min:8',
            'photo_profile' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'role' => 'required|string|in:admin,personel,kordinator_angkatan,kordinator_matra'
        ]);

        DB::beginTransaction();
        try {
            $roleName = $request->input('role', 'personel');
            $role = Role::where('name', $roleName)->first();
            
            $user = User::create([
                'uuid' => Str::uuid(),
                'role_id' => $role->id,
                'username' => !empty($validated['nikc']) ? trim($validated['nikc']) : $validated['nik'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => true
            ]);

            // Standard Laravel upload (Disimpan di disk private aman)
            $fileName = $request->file('photo_profile')->store('personel/photos', 'private');

            $otpCode   = str_pad(strval(rand(0, 999999)), 6, '0', STR_PAD_LEFT);
            $expiredAt = now()->addHours(72);

            $personel = Personel::create(array_merge($validated, [
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'photo_profile' => $fileName,
                'status_profile' => 'BELUM_LENGKAP',
                'face_verified' => false,
                'manual_otp' => $otpCode,
                'manual_otp_expired_at' => $expiredAt,
                'pob' => '-', 
                'address' => '-', 'city' => '-', 'district' => '-', 'village' => '-', 'postal_code' => '-'
            ]));

            Registration::create([
                'personel_id' => $personel->id,
                'status_verification' => 'APPROVED',
                'verified_by' => auth()->id(),
                'verified_at' => now()
            ]);

            DB::commit();

            // Kirim Email Informasi Akun + Lampiran PDF ke Email Personel
            if ($user->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(
                        new \App\Mail\PersonelAccountCreatedMail($personel, $validated['password'])
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Gagal mengirimkan email kredensial akun ke {$user->email}: " . $e->getMessage());
                }
            }

            $pdfUrl = route('admin.personel.print-account', [
                'uuid' => $personel->uuid,
                'pwd'  => $validated['password']
            ]);

            return redirect()->route('admin.personel.index')->with([
                'success' => 'Data personel baru berhasil didaftarkan langsung ke sistem.',
                'created_credentials' => [
                    'uuid'      => $personel->uuid,
                    'full_name' => $personel->full_name,
                    'nikc'      => $personel->nikc ?: $personel->nik,
                    'pangkat'   => $personel->pangkat,
                    'matra'     => $personel->matra,
                    'password'  => $validated['password'],
                    'pdf_url'   => $pdfUrl,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    /**
     * Lembar Formulir Pengubahan Data Personel
     */
    public function edit($uuid)
    {
        $personel = Personel::where('uuid', $uuid)->with('user.role')->firstOrFail();
        return Inertia::render('Admin/Personel/Edit', [
            'personel' => $personel,
            'pangkatOptions' => MasterKepangkatan::where('is_active', true)
                ->orderBy('urutan')
                ->get(['nama', 'kelompok_nikc', 'klaim_langsung']),
        ]);
    }

    /**
     * Memproses Pembaruan Amandemen Data Personel & Berkas Pasfoto
     */
    public function update(Request $request, $uuid)
    {
        if ($request->has('nikc')) {
            $request->merge(['nikc' => SkepData::reconstructNikc($request->nikc)]);
        }

        if ($request->filled('pangkat')) {
            $request->merge([
                'pangkat' => MasterKepangkatan::canonicalName($request->input('pangkat')),
            ]);
        }

        $personel = Personel::where('uuid', $uuid)->firstOrFail();
        $user = User::findOrFail($personel->user_id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone_number' => 'required|string|max:20',
            'matra' => 'required|in:AD,AL,AU',
            'angkatan' => 'required|digits:4',
            'pangkat' => ['required', Rule::exists('master_kepangkatan', 'nama')->where('is_active', true)],
            'nikc' => [
                'nullable',
                'digits:17',
                'numeric',
                new NikcFormatRule(null, null, $request->input('pangkat')),
                Rule::unique('personels', 'nikc')->ignore($personel->id),
            ],
            'photo_profile' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'role' => 'required|string|in:admin,personel,kordinator_angkatan,kordinator_matra'
        ]);

        DB::beginTransaction();
        try {
            $oldRole = $user->role ? $user->role->name : 'personel';
            $roleName = $request->input('role', 'personel');
            $role = Role::where('name', $roleName)->first();

            $user->update([
                'username' => !empty($validated['nikc']) ? trim($validated['nikc']) : $user->username,
                'email' => $validated['email'],
                'role_id' => $role->id
            ]);

            // Jika role berubah, kirim notifikasi WA
            if (strcasecmp($oldRole, $role->name) !== 0) {
                $roleLabels = [
                    'admin' => 'Administrator',
                    'personel' => 'Personel Komcad',
                    'kordinator_angkatan' => 'Koordinator Angkatan',
                    'kordinator_matra' => 'Koordinator Matra',
                    'komandan' => 'Komandan Satuan'
                ];
                $newRoleLabel = $roleLabels[$role->name] ?? $role->name;
                $msg = "Halo *{$personel->full_name}*, peran (role) akun Anda di SISFOPERS telah diubah oleh Administrator menjadi *{$newRoleLabel}*. Silakan login kembali untuk menikmati akses baru Anda.";
                \App\Services\WhatsappService::sendMessage($personel->phone_number, $msg);
            }

            if ($request->hasFile('photo_profile')) {
                if ($personel->photo_profile) {
                    Storage::disk('private')->delete($personel->photo_profile);
                    Storage::disk('private')->delete($personel->photo_profile);
                }
                $fileName = $request->file('photo_profile')->store('personel/photos', 'private');
                $personel->photo_profile = $fileName;
            }

            // Update data personel termasuk field pangkat & nikc terbaru
            $personel->update(array_diff_key($validated, ['photo_profile' => '', 'role' => '']));
            
            DB::commit();
            return redirect()->route('admin.personel.index')->with('success', 'Profil personel berhasil dimutakhirkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui data: ' . $e->getMessage()]);
        }
    }

    /**
     * Menghapus Akun Akses Beserta Berkas Detail Anggota Terkait
     */
    public function destroy($uuid)
    {
        DB::beginTransaction();
        try {
            $personel = Personel::where('uuid', $uuid)->firstOrFail();
            
            // Hapus secara permanen personel, seluruh berkas fisik, tabel relasi, dan akun User (email & username)
            Personel::purgePersonelCompletely($personel);

            DB::commit();
            return redirect()->route('admin.personel.index')->with('success', 'Data personel beserta seluruh berkas fisik dan akun aksesnya berhasil dihapus secara permanen dari sistem.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus data: ' . $e->getMessage()]);
        }
    }
}
