<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekta;
use App\Models\Personel;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EktaAdminController extends Controller
{
    /**
     * Tampilkan halaman utama Manajemen E-KTA Admin
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status', 'all');

        $query = Personel::with(['user', 'sinyalmen', 'ekta'])
            ->orderBy('full_name', 'ASC');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nikc', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('pangkat', 'like', "%{$search}%")
                  ->orWhere('matra', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'terbit') {
            $query->whereHas('ekta', function ($q) {
                $q->where('status', 'TERBIT');
            });
        } elseif ($statusFilter === 'belum_terbit') {
            $query->where(function ($q) {
                $q->whereDoesntHave('ekta')
                  ->orWhereHas('ekta', function ($sq) {
                      $sq->where('status', '!=', 'TERBIT');
                  });
            });
        }

        $personels = $query->paginate(12)->withQueryString();

        // Agregasi statistik
        $totalPersonel = Personel::count();
        $totalTerbit = Ekta::where('status', 'TERBIT')->count();
        $totalBelumTerbit = max(0, $totalPersonel - $totalTerbit);

        // Ambil konfigurasi E-KTA dari tabel settings
        $settings = Setting::whereIn('key', [
            'ekta_logo_komcad',
            'ekta_signature',
            'ekta_stamp',
            'ekta_signer_name',
            'ekta_signer_rank',
            'ekta_signer_title',
            'logo_tni',
        ])->pluck('value', 'key')->toArray();

        $ektaSettings = [
            'logo_komcad'  => $settings['ekta_logo_komcad'] ?? null,
            'signature'    => $settings['ekta_signature'] ?? null,
            'stamp'        => $settings['ekta_stamp'] ?? null,
            'signer_name'  => $settings['ekta_signer_name'] ?? 'Sri Yanto, S.T.',
            'signer_rank'  => $settings['ekta_signer_rank'] ?? 'Laksamana Muda TNI',
            'signer_title' => $settings['ekta_signer_title'] ?? 'Direktur Jenderal Potensi Pertahanan',
            'logo_tni'     => $settings['logo_tni'] ?? null,
        ];

        return Inertia::render('Admin/Ekta/Index', [
            'personels'     => $personels,
            'filters'       => [
                'search' => $search,
                'status' => $statusFilter,
            ],
            'stats'         => [
                'total_personel'     => $totalPersonel,
                'total_terbit'       => $totalTerbit,
                'total_belum_terbit' => $totalBelumTerbit,
            ],
            'ektaSettings'  => $ektaSettings,
        ]);
    }

    /**
     * Tampilkan detail E-KTA untuk satu personel tertentu
     */
    public function show($id)
    {
        $personel = Personel::with(['user', 'sinyalmen', 'ekta'])->findOrFail($id);

        $settings = Setting::whereIn('key', [
            'ekta_logo_komcad',
            'ekta_signature',
            'ekta_stamp',
            'ekta_signer_name',
            'ekta_signer_rank',
            'ekta_signer_title',
        ])->pluck('value', 'key')->toArray();

        $ektaSettings = [
            'logo_komcad'  => $settings['ekta_logo_komcad'] ?? null,
            'signature'    => $settings['ekta_signature'] ?? null,
            'stamp'        => $settings['ekta_stamp'] ?? null,
            'signer_name'  => $settings['ekta_signer_name'] ?? 'Sri Yanto, S.T.',
            'signer_rank'  => $settings['ekta_signer_rank'] ?? 'Laksamana Muda TNI',
            'signer_title' => $settings['ekta_signer_title'] ?? 'Direktur Jenderal Potensi Pertahanan',
        ];

        return Inertia::render('Admin/Ekta/Show', [
            'personel'     => $personel,
            'ektaSettings' => $ektaSettings,
        ]);
    }

    /**
     * Kirim OTP otorisasi ke email admin saat hendak menerbitkan E-KTA
     */
    public function requestOtp(Request $request)
    {
        $admin = auth()->user();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Sesi login tidak sah.'], 401);
        }

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        session([
            'ekta_otp_code'       => $otp,
            'ekta_otp_expires_at' => $expiresAt->timestamp,
            'ekta_otp_verified'   => false,
        ]);

        $recipientEmail = $admin->email ?: 'admin@sisfopers.site';

        try {
            if ($admin->email) {
                Mail::to($admin->email)->send(
                    new \App\Mail\OtpNotificationMail(
                        $admin->username ?: 'Administrator Mabes',
                        $admin->username ?: 'MABES-ADMIN',
                        $otp,
                        'Otorisasi Penerbitan E-KTA',
                        '10 Menit'
                    )
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirimkan email OTP E-KTA: " . $e->getMessage());
        }

        return response()->json([
            'success'   => true,
            'message'   => "Kode OTP 6-digit berhasil dikirimkan ke email resmi {$recipientEmail}.",
            'debug_otp' => config('app.debug') ? $otp : null,
        ]);
    }

    /**
     * Verifikasi kode OTP yang dimasukkan oleh admin
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $savedOtp = session('ekta_otp_code');
        $expiresAt = session('ekta_otp_expires_at');

        if (!$savedOtp || !$expiresAt || now()->timestamp > $expiresAt) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP telah kadaluarsa. Silakan minta kode OTP baru.',
            ], 422);
        }

        if (trim($request->otp) !== (string) $savedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP yang dimasukkan tidak cocok. Periksa kembali kotak masuk email Anda.',
            ], 422);
        }

        session(['ekta_otp_verified' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Otorisasi OTP berhasil disahkan. Silakan periksa konfirmasi kelengkapan data E-KTA.',
        ]);
    }

    /**
     * Terbitkan E-KTA setelah OTP terverifikasi dan konfirmasi disetujui
     */
    public function publish(Request $request, $id)
    {
        $personel = Personel::with(['user', 'sinyalmen', 'ekta'])->findOrFail($id);

        $request->validate([
            'nomor_urut'       => 'nullable|string|max:50',
            'tahun_lulus'      => 'nullable|string|max:10',
            'pangkat'          => 'nullable|string|max:100',
            'jabatan'          => 'nullable|string|max:100',
            'kesatuan_matra'   => 'nullable|string|max:100',
            'berlaku_sampai'   => 'nullable|string|max:100',
            'tinggi_berat'     => 'nullable|string|max:50',
            'rambut'           => 'nullable|string|max:50',
            'mata'             => 'nullable|string|max:50',
            'golongan_darah'   => 'nullable|string|max:10',
            'tempat_lahir'     => 'nullable|string|max:100',
            'tanggal_lahir'    => 'nullable|string|max:100',
            'agama'            => 'nullable|string|max:50',
            'alamat'           => 'nullable|string|max:500',
            'tanda_kehormatan' => 'nullable|string|max:500',
        ]);

        $tahunLulus = $request->tahun_lulus ?: ($personel->angkatan ?: date('Y'));
        $nomorUrut = $request->nomor_urut ?: null;

        // Penomoran KTA: No.[nomor_urut]/KTA KC/[tahun] atau No. ...../KTA KC/[tahun]
        if (!empty($nomorUrut)) {
            $nomorKta = "No.{$nomorUrut}/KTA KC/{$tahunLulus}";
        } else {
            $nomorKta = "No. ...../KTA KC/{$tahunLulus}";
        }

        $pangkat = $request->pangkat ?: ($personel->pangkat ?: 'Anggota Komcad');
        
        // Auto jabatan jika tidak diisi
        $isPerwira = str_contains(strtoupper($pangkat), 'LET') ||
                     str_contains(strtoupper($pangkat), 'KAP') ||
                     str_contains(strtoupper($pangkat), 'MAY') ||
                     str_contains(strtoupper($pangkat), 'KOL');
        $defaultJabatan = $isPerwira ? 'Perwira Komcad' : 'Anggota Komcad';

        // Auto kesatuan matra
        $matraMap = [
            'AD' => 'Matra Darat',
            'AL' => 'Matra Laut',
            'AU' => 'Matra Udara',
        ];
        $kesatuanMatra = $request->kesatuan_matra ?: ($matraMap[$personel->matra] ?? 'Matra Darat');

        // Sinyalemen fallback
        $sinyalmen = $personel->sinyalmen;
        $tinggiBerat = $request->tinggi_berat 
            ?: ($sinyalmen && $sinyalmen->tinggi_badan && $sinyalmen->berat_badan ? "{$sinyalmen->tinggi_badan}/{$sinyalmen->berat_badan}" : '165/60');
        $rambut = $request->rambut ?: ($sinyalmen?->rambut ?: 'Lurus');
        $mata = $request->mata ?: ($sinyalmen?->mata ?: 'Hitam');
        $golDarah = $request->golongan_darah ?: ($sinyalmen?->golongan_darah ?: 'O');

        // Format tanggal lahir
        $tglLahirFormatted = '-';
        if ($personel->dob) {
            try {
                $tglLahirFormatted = \Carbon\Carbon::parse($personel->dob)->translatedFormat('d F Y');
            } catch (\Throwable $e) {
                $tglLahirFormatted = (string) $personel->dob;
            }
        }
        $tglLahir = $request->tanggal_lahir ?: $tglLahirFormatted;

        $verifyCode = 'KTA-' . $tahunLulus . '-' . strtoupper(Str::random(8));

        $ekta = Ekta::updateOrCreate(
            ['personel_id' => $personel->id],
            [
                'uuid'             => (string) Str::uuid(),
                'nomor_kta'        => $nomorKta,
                'nomor_urut'       => $nomorUrut,
                'tahun_lulus'      => $tahunLulus,
                'pangkat'          => $pangkat,
                'jabatan'          => $request->jabatan ?: $defaultJabatan,
                'kesatuan_matra'   => $kesatuanMatra,
                'berlaku_sampai'   => $request->berlaku_sampai ?: 'Selama Menjadi Anggota Komcad',
                'tinggi_berat'     => $tinggiBerat,
                'rambut'           => $rambut,
                'mata'             => $mata,
                'golongan_darah'   => $golDarah,
                'tempat_lahir'     => $request->tempat_lahir ?: ($personel->pob ?: '-'),
                'tanggal_lahir'    => $tglLahir,
                'agama'            => $request->agama ?: 'Islam',
                'alamat'           => $request->alamat ?: ($personel->address ?: '-'),
                'tanda_kehormatan' => $request->tanda_kehormatan ?: null,
                'verify_code'      => $verifyCode,
                'status'           => 'TERBIT',
                'issued_at'        => now(),
                'issued_by'        => auth()->id(),
            ]
        );

        // Catat Audit Trail
        AuditLog::record(
            'CREATE',
            'Ekta',
            $ekta->id,
            null,
            [
                'nomor_kta'   => $ekta->nomor_kta,
                'personel_id' => $personel->id,
                'nama'        => $personel->full_name,
                'nikc'        => $personel->nikc,
            ]
        );

        // Hapus session otorisasi OTP
        session()->forget(['ekta_otp_code', 'ekta_otp_expires_at', 'ekta_otp_verified']);

        return redirect()->back()->with('success', "E-KTA atas nama {$personel->full_name} berhasil diterbitkan dengan nomor {$ekta->nomor_kta}.");
    }

    /**
     * Perbarui konfigurasi E-KTA (Logo Komcad, Tanda Tangan, Stempel, Pejabat)
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'ekta_logo_komcad'  => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'ekta_signature'    => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'ekta_stamp'        => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'ekta_signer_name'  => 'nullable|string|max:150',
            'ekta_signer_rank'  => 'nullable|string|max:150',
            'ekta_signer_title' => 'nullable|string|max:200',
        ]);

        if ($request->hasFile('ekta_logo_komcad')) {
            $path = $request->file('ekta_logo_komcad')->store('ekta', 'public');
            Setting::updateOrCreate(['key' => 'ekta_logo_komcad'], ['value' => '/storage/' . $path]);
        }

        if ($request->hasFile('ekta_signature')) {
            $path = $request->file('ekta_signature')->store('ekta', 'public');
            Setting::updateOrCreate(['key' => 'ekta_signature'], ['value' => '/storage/' . $path]);
        }

        if ($request->hasFile('ekta_stamp')) {
            $path = $request->file('ekta_stamp')->store('ekta', 'public');
            Setting::updateOrCreate(['key' => 'ekta_stamp'], ['value' => '/storage/' . $path]);
        }

        if ($request->filled('ekta_signer_name')) {
            Setting::updateOrCreate(['key' => 'ekta_signer_name'], ['value' => $request->ekta_signer_name]);
        }

        if ($request->filled('ekta_signer_rank')) {
            Setting::updateOrCreate(['key' => 'ekta_signer_rank'], ['value' => $request->ekta_signer_rank]);
        }

        if ($request->filled('ekta_signer_title')) {
            Setting::updateOrCreate(['key' => 'ekta_signer_title'], ['value' => $request->ekta_signer_title]);
        }

        return redirect()->back()->with('success', 'Pengaturan aset logo, tanda tangan, dan pejabat E-KTA berhasil disimpan.');
    }
}
