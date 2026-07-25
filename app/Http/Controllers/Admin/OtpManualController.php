<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\AuditLog;
use App\Services\WhatsappService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OtpManualController extends Controller
{
    /**
     * Tampilkan daftar personel yang sudah APPROVED tapi belum verifikasi OTP (face_verified = false)
     */
    public function index(Request $request)
    {
        $query = Personel::with(['user', 'registration'])
            ->where('face_verified', false)
            ->whereHas('registration', function ($q) {
                $q->where('status_verification', 'APPROVED');
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nikc', 'like', "%{$search}%");
            });
        }

        if ($request->filled('matra')) {
            $query->where('matra', $request->matra);
        }

        $personels = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/OtpManual/Index', [
            'personels' => $personels,
            'filters'   => $request->only(['search', 'matra']),
        ]);
    }

    /**
     * Generate OTP 6 digit untuk personel yang dipilih, simpan ke DB
     */
    public function generate(Request $request, $id)
    {
        $personel = Personel::with('user')->findOrFail($id);

        if ($personel->face_verified) {
            return back()->with('error', 'Personel ini sudah melakukan verifikasi OTP sebelumnya.');
        }

        $otpCode   = str_pad(strval(rand(0, 999999)), 6, '0', STR_PAD_LEFT);
        $expiredAt = now()->addHours(72);

        $personel->update([
            'manual_otp'             => $otpCode,
            'manual_otp_expired_at'  => $expiredAt,
            'manual_otp_printed_at'  => null,   // reset status cetak agar muncul kembali di cetak massal
        ]);

        $userEmail = $personel->user?->email;
        $sentEmailNotice = '';
        if ($userEmail) {
            try {
                \Illuminate\Support\Facades\Mail::to($userEmail)->send(
                    new \App\Mail\OtpNotificationMail(
                        $personel->full_name,
                        $personel->nikc,
                        $otpCode,
                        'Verifikasi Pendaftaran (OTP Manual)',
                        '72 Jam'
                    )
                );
                $sentEmailNotice = " & Email OTP berhasil dikirimkan ke {$userEmail}";
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal kirim email OTP manual ke {$userEmail}: " . $e->getMessage());
            }
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'GENERATE_MANUAL_OTP',
            'model_type' => 'App\Models\Personel',
            'model_id'   => $personel->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "OTP untuk {$personel->full_name} berhasil di-generate: {$otpCode}{$sentEmailNotice}");
    }

    /**
     * Generate OTP massal untuk SEMUA personel yang belum verifikasi
     */
    public function generateAll(Request $request)
    {
        $personels = Personel::where('face_verified', false)
            ->whereHas('registration', fn($q) => $q->where('status_verification', 'APPROVED'))
            ->get();

        if ($personels->isEmpty()) {
            return back()->with('error', 'Tidak ada personel yang perlu di-generate OTP.');
        }

        $expiredAt = now()->addHours(72);
        $count     = 0;

        foreach ($personels as $personel) {
            $otpCode = str_pad(strval(rand(0, 999999)), 6, '0', STR_PAD_LEFT);
            $personel->update([
                'manual_otp'             => $otpCode,
                'manual_otp_expired_at'  => $expiredAt,
                'manual_otp_printed_at'  => null,   // reset status cetak
            ]);
            $count++;
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'GENERATE_MANUAL_OTP_BULK',
            'model_type' => 'App\Models\Personel',
            'model_id'   => 0,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "OTP berhasil di-generate untuk {$count} personel. Berlaku 72 jam. Silakan cetak PDF.");
    }

    /**
     * Verifikasi manual OTP yang diinput personel dari lembar cetak
     * (Digunakan oleh personel di halaman verifikasi; dipanggil dari FaceVerificationController)
     */
    public function verifyManual(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $user = Auth::user();
        $personel = $user->personel;

        if (!$personel) {
            return back()->withErrors(['otp' => 'Profil personel tidak ditemukan.']);
        }

        if (!$personel->manual_otp) {
            return back()->withErrors(['otp' => 'Belum ada OTP manual yang di-generate oleh admin untuk akun Anda. Hubungi administrator.']);
        }

        if (now()->greaterThan($personel->manual_otp_expired_at)) {
            return back()->withErrors(['otp' => 'OTP manual Anda telah kedaluwarsa. Hubungi administrator untuk generate ulang.']);
        }

        if ($personel->manual_otp !== $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP tidak cocok. Periksa kembali lembar cetak OTP Anda.']);
        }

        // OTP valid — tandai akun sebagai terverifikasi
        $personel->update([
            'face_verified'         => true,
            'manual_otp'            => null,
            'manual_otp_expired_at' => null,
        ]);

        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'VERIFY_MANUAL_OTP',
            'model_type' => 'App\Models\Personel',
            'model_id'   => $personel->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $user->notify(new \App\Notifications\SystemNotification(
            'Verifikasi Akun Sukses',
            'Akun Sisfopers Anda telah berhasil diverifikasi via OTP Manual.',
            'success',
            route('personel.dashboard')
        ));

        return redirect()->route('personel.sinyalmen.create');
    }

    /**
     * Download PDF OTP untuk satu personel
     * — setelah dicetak, tandai manual_otp_printed_at agar tidak muncul lagi di cetak massal berikutnya
     */
    public function printPdf($id)
    {
        $personel = Personel::with(['user', 'registration'])->findOrFail($id);

        if (!$personel->manual_otp) {
            return back()->with('error', 'Generate OTP terlebih dahulu sebelum mencetak PDF.');
        }

        $currentUser     = Auth::user();
        $currentPersonel = $currentUser?->personel;

        $signerName    = $currentPersonel?->full_name ?? 'Administrator';
        $signerPangkat = $this->formatRank($currentPersonel?->pangkat);
        $signerNikc    = $currentPersonel?->nikc ?? '-';
        $signerJabatan = $currentUser?->role?->display_name ?? 'Administrator Sistem';

        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        $nomorSurat = 'OTP/' . str_pad($personel->id, 3, '0', STR_PAD_LEFT) . '/PERS/' . $romans[now()->month] . '/' . now()->year;

        $docVerif = \App\Models\DocumentVerification::createRecord(
            'OTP_MANUAL',
            'LEMBAR KODE OTP VERIFIKASI AKUN',
            $personel->full_name,
            $personel->nikc ?: $personel->nik,
            $signerName,
            $signerPangkat,
            ['nomor_surat' => $nomorSurat, 'matra' => $personel->matra]
        );

        $verifyUrl = route('public.verify-doc', $docVerif->verify_code);
        $qrCodeBase64 = \App\Services\QrCodeService::generateBase64($verifyUrl);

        $pdf = Pdf::loadView('reports.otp_pdf', [
            'personel'      => $personel,
            'pangkatFull'   => $this->formatRank($personel->pangkat),
            'expiredAt'     => $personel->manual_otp_expired_at
                                ? $this->formatDateId($personel->manual_otp_expired_at)
                                : '-',
            'generatedAt'   => $this->formatDateId(now()),
            'signerName'    => $signerName,
            'signerPangkat' => $signerPangkat,
            'signerNikc'    => $signerNikc,
            'signerJabatan' => $signerJabatan,
            'nomorSurat'    => $nomorSurat,
            'verifyCode'    => $docVerif->verify_code,
            'verifyUrl'     => $verifyUrl,
            'qrCodeBase64'  => $qrCodeBase64,
        ])->setPaper('a4', 'portrait');

        // Tandai sudah dicetak — tidak akan muncul di cetak massal berikutnya
        $personel->update(['manual_otp_printed_at' => now()]);

        return $pdf->download('OTP_' . str_replace(' ', '_', $personel->full_name) . '_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Cetak PDF OTP massal — HANYA yang belum pernah dicetak (manual_otp_printed_at IS NULL)
     * Setelah PDF di-generate, semua personel yang termasuk langsung ditandai sebagai sudah dicetak.
     */
    public function printBulkPdf()
    {
        // Hanya ambil yang OTP-nya belum pernah dicetak
        $personels = Personel::with(['user', 'registration'])
            ->where('face_verified', false)
            ->whereNotNull('manual_otp')
            ->whereNull('manual_otp_printed_at')          // <-- filter belum dicetak
            ->whereHas('registration', fn($q) => $q->where('status_verification', 'APPROVED'))
            ->get();

        if ($personels->isEmpty()) {
            return back()->with('error', 'Tidak ada OTP baru yang perlu dicetak. Semua OTP sudah pernah dicetak sebelumnya. Generate OTP baru terlebih dahulu.');
        }

        $currentUser     = Auth::user();
        $currentPersonel = $currentUser?->personel;
        $signerName    = $currentPersonel?->full_name ?? 'Administrator';
        $signerPangkat = $this->formatRank($currentPersonel?->pangkat);
        $signerNikc    = $currentPersonel?->nikc ?? '-';
        $signerJabatan = $currentUser?->role?->display_name ?? 'Administrator Sistem';

        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        $nomorSurat = 'OTP/BULK/PERS/' . $romans[now()->month] . '/' . now()->year;

        $docVerif = \App\Models\DocumentVerification::createRecord(
            'OTP_MANUAL_BULK',
            'LEMBAR KODE OTP VERIFIKASI AKUN (MASSAL)',
            'Daftar Massal (' . $personels->count() . ' Personel)',
            'Cetak Massal OTP Baru',
            $signerName,
            $signerPangkat,
            ['nomor_surat' => $nomorSurat]
        );

        $verifyUrl = route('public.verify-doc', $docVerif->verify_code);
        $qrCodeBase64 = \App\Services\QrCodeService::generateBase64($verifyUrl);

        $pdf = Pdf::loadView('reports.otp_bulk_pdf', [
            'personels'     => $personels,
            'generatedAt'   => $this->formatDateId(now()),
            'signerName'    => $signerName,
            'signerPangkat' => $signerPangkat,
            'signerNikc'    => $signerNikc,
            'signerJabatan' => $signerJabatan,
            'nomorSurat'    => $nomorSurat,
            'verifyCode'    => $docVerif->verify_code,
            'verifyUrl'     => $verifyUrl,
            'qrCodeBase64'  => $qrCodeBase64,
        ])->setPaper('a4', 'portrait');

        // Tandai semua yang masuk PDF ini sebagai sudah dicetak
        $ids = $personels->pluck('id');
        Personel::whereIn('id', $ids)->update(['manual_otp_printed_at' => now()]);

        return $pdf->download('OTP_Massal_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Format tanggal ke format Indonesia: dd Bulan YYYY HH:mm WIB
     */
    private function formatDateId($date): string
    {
        $bulan = [
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];
        $dt = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return $dt->format('d') . ' ' . $bulan[$dt->month] . ' ' . $dt->format('Y H:i') . ' WIB';
    }

    private function formatRank($pangkat)
    {
        $map = [
            'Lettu'      => 'Letnan Satu (KC)',
            'Letda'      => 'Letnan Dua (KC)',
            'Letda (W)'  => 'Letnan Dua (KC)',
            'Serda'      => 'Sersan Dua (KC)',
            'Serda (W)'  => 'Sersan Dua (KC)',
            'Prada'      => 'Prajurit Dua (KC)',
        ];
        return $map[$pangkat] ?? ($pangkat ? $pangkat . ' (KC)' : 'Administrator');
    }
}
