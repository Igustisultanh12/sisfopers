<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\AuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OtpResetPasswordController extends Controller
{
    /**
     * Tampilkan daftar personel aktif yang dapat di-reset password-nya
     */
    public function index(Request $request)
    {
        $query = Personel::with(['user', 'registration'])
            ->whereHas('user', function ($q) {
                $q->where('is_active', true);
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

        return Inertia::render('Admin/OtpResetPassword/Index', [
            'personels' => $personels,
            'filters'   => $request->only(['search', 'matra']),
        ]);
    }

    /**
     * Generate OTP Reset Password 6 digit untuk personel pilihan, simpan ke DB
     */
    public function generate(Request $request, $id)
    {
        $personel = Personel::with('user')->findOrFail($id);

        $otpCode   = str_pad(strval(rand(0, 999999)), 6, '0', STR_PAD_LEFT);
        $expiredAt = now()->addHours(24); // OTP Reset berlaku 24 jam

        $personel->update([
            'reset_password_otp'             => $otpCode,
            'reset_password_otp_expired_at'  => $expiredAt,
            'reset_password_otp_printed_at'  => null, // Reset status cetak
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
                        'Reset Password',
                        '24 Jam'
                    )
                );
                $sentEmailNotice = " & email OTP dikirimkan ke {$userEmail}";
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal kirim email OTP reset ke {$userEmail}: " . $e->getMessage());
                $sentEmailNotice = ' (Catatan: Pengiriman email gagal, silakan periksa konfigurasi SMTP Gmail)';
            }
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'GENERATE_RESET_PASSWORD_OTP',
            'model_type' => 'App\Models\Personel',
            'model_id'   => $personel->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "OTP Reset Password untuk {$personel->full_name} berhasil di-generate: {$otpCode}{$sentEmailNotice}");
    }

    /**
     * Generate OTP Reset Password massal
     */
    public function generateAll(Request $request)
    {
        $personels = Personel::whereHas('user', function ($q) {
            $q->where('is_active', true);
        })->get();

        if ($personels->isEmpty()) {
            return back()->with('error', 'Tidak ada personel aktif untuk di-generate OTP Reset Password.');
        }

        $expiredAt = now()->addHours(24);
        $count     = 0;

        foreach ($personels as $personel) {
            $otpCode = str_pad(strval(rand(0, 999999)), 6, '0', STR_PAD_LEFT);
            $personel->update([
                'reset_password_otp'             => $otpCode,
                'reset_password_otp_expired_at'  => $expiredAt,
                'reset_password_otp_printed_at'  => null,
            ]);
            $count++;
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'GENERATE_RESET_PASSWORD_OTP_BULK',
            'model_type' => 'App\Models\Personel',
            'model_id'   => 0,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "OTP Reset Password berhasil di-generate untuk {$count} personel. Berlaku 24 jam.");
    }

    /**
     * Download PDF OTP Reset Password untuk satu personel
     */
    public function printPdf($id)
    {
        $personel = Personel::with(['user', 'registration'])->findOrFail($id);

        if (!$personel->reset_password_otp) {
            return back()->with('error', 'Generate OTP Reset Password terlebih dahulu sebelum mencetak PDF.');
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

        $nomorSurat = 'OTP-RST/' . str_pad($personel->id, 3, '0', STR_PAD_LEFT) . '/PERS/' . $romans[now()->month] . '/' . now()->year;

        $docVerif = \App\Models\DocumentVerification::createRecord(
            'OTP_RESET_PASSWORD',
            'LEMBAR KODE OTP RESET PASSWORD',
            $personel->full_name,
            $personel->nikc ?: $personel->nik,
            $signerName,
            $signerPangkat,
            ['nomor_surat' => $nomorSurat, 'matra' => $personel->matra]
        );

        $verifyUrl = route('public.verify-doc', $docVerif->verify_code);
        $qrCodeBase64 = \App\Services\QrCodeService::generateBase64($verifyUrl);

        $pdf = Pdf::loadView('reports.reset_password_otp_pdf', [
            'personel'      => $personel,
            'pangkatFull'   => $this->formatRank($personel->pangkat),
            'expiredAt'     => $personel->reset_password_otp_expired_at
                                ? $this->formatDateId($personel->reset_password_otp_expired_at)
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

        $personel->update(['reset_password_otp_printed_at' => now()]);

        return $pdf->download('OTP_ResetPassword_' . str_replace(' ', '_', $personel->full_name) . '_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Cetak PDF OTP Reset Password massal (Hanya yang belum pernah dicetak)
     */
    public function printBulkPdf()
    {
        $personels = Personel::with(['user', 'registration'])
            ->whereNotNull('reset_password_otp')
            ->whereNull('reset_password_otp_printed_at')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get();

        if ($personels->isEmpty()) {
            return back()->with('error', 'Tidak ada OTP Reset Password baru yang perlu dicetak. Silakan generate ulang.');
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

        $nomorSurat = 'OTP-RST/BULK/PERS/' . $romans[now()->month] . '/' . now()->year;

        $docVerif = \App\Models\DocumentVerification::createRecord(
            'OTP_RESET_PASSWORD_BULK',
            'LEMBAR KODE OTP RESET PASSWORD (MASSAL)',
            'Daftar Massal (' . $personels->count() . ' Personel)',
            'Cetak Massal OTP Reset Password',
            $signerName,
            $signerPangkat,
            ['nomor_surat' => $nomorSurat]
        );

        $verifyUrl = route('public.verify-doc', $docVerif->verify_code);
        $qrCodeBase64 = \App\Services\QrCodeService::generateBase64($verifyUrl);

        $pdf = Pdf::loadView('reports.reset_password_otp_bulk_pdf', [
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

        $ids = $personels->pluck('id');
        Personel::whereIn('id', $ids)->update(['reset_password_otp_printed_at' => now()]);

        return $pdf->download('OTP_ResetPassword_Massal_' . now()->format('Ymd_His') . '.pdf');
    }

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
