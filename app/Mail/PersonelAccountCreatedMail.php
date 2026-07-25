<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\Personel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PersonelAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Personel $personel;
    public string $password;
    public string $formattedName;
    public string $logoUrl;

    public function __construct(Personel $personel, string $password)
    {
        $this->personel = $personel;
        $this->password = $password;
        $this->formattedName = $this->formatMilitaryTitle($personel);

        $savedLogo = Setting::where('key', 'logo_tni')->value('value');
        if ($savedLogo) {
            $this->logoUrl = str_starts_with($savedLogo, 'http') ? $savedLogo : url($savedLogo);
        } else {
            $this->logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/b/b5/Tentara_Nasional_Indonesia_insignia.svg';
        }
    }

    private function formatMilitaryTitle(Personel $personel): string
    {
        $fullName = rtrim(trim($personel->full_name ?? 'Prajurit'), ',.');
        $pangkat  = trim($personel->pangkat ?? '');

        if (empty($pangkat)) {
            return 'Prajurit KC ' . $fullName;
        }

        if (str_contains(strtoupper($pangkat), 'KC')) {
            return $pangkat . ' ' . $fullName;
        }

        return $pangkat . ' KC ' . $fullName;
    }

    public function build()
    {
        // Generate PDF Lampiran Informasi Akun
        $currentUser     = auth()->user();
        $currentPersonel = $currentUser?->personel;
        $signerName      = $currentPersonel?->full_name ?? 'Administrator Utama';
        $signerPangkat   = $currentPersonel?->pangkat ?? 'Administrator Sistem';

        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $nomorSurat = 'REG/' . str_pad($this->personel->id, 3, '0', STR_PAD_LEFT) . '/PERS/' . $romans[now()->month] . '/' . now()->year;

        $docVerif = \App\Models\DocumentVerification::createRecord(
            'INFORMASI_AKUN',
            'SURAT INFORMASI KREDENSIAL AKUN PERSONEL',
            $this->personel->full_name,
            $this->personel->nikc ?: $this->personel->nik,
            $signerName,
            $signerPangkat,
            ['nomor_surat' => $nomorSurat, 'matra' => $this->personel->matra, 'angkatan' => $this->personel->angkatan]
        );

        $verifyUrl = route('public.verify-doc', $docVerif->verify_code);
        $qrCodeBase64 = \App\Services\QrCodeService::generateBase64($verifyUrl);

        $pdf = Pdf::loadView('reports.account_info_pdf', [
            'personel'      => $this->personel,
            'password'      => $this->password,
            'nomorSurat'    => $nomorSurat,
            'generatedAt'   => now()->translatedFormat('d F Y H:i') . ' WIB',
            'signerName'    => $signerName,
            'signerPangkat' => $signerPangkat,
            'verifyCode'    => $docVerif->verify_code,
            'verifyUrl'     => $verifyUrl,
            'qrCodeBase64'  => $qrCodeBase64,
        ])->setPaper('a4', 'portrait');

        $fileName = 'INFORMASI_AKUN_' . str_replace(' ', '_', $this->personel->full_name) . '_' . ($this->personel->nikc ?: $this->personel->nik) . '.pdf';

        return $this->subject('[SISFOPERSKC] Informasi Akun & Kredensial Akses Personel Baru')
                    ->html($this->htmlContent())
                    ->attachData(
                        $pdf->output(),
                        $fileName,
                        ['mime' => 'application/pdf']
                    );
    }

    private function htmlContent(): string
    {
        $loginUrl = route('login');
        $nikc = $this->personel->nikc ?: $this->personel->nik;
        $matra = strtoupper($this->personel->matra ?? 'AD');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Informasi Akun SISFOPERSKC</title>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 25px 15px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;'>
                
                <!-- HEADER LOGO -->
                <div style='background-color: #0f172a; padding: 28px 24px; text-align: center; border-bottom: 4px solid #2563eb;'>
                    <img src='{$this->logoUrl}' alt='Logo Sisfoperskc' style='height: 56px; width: auto; max-width: 160px; object-contain: fit; margin-bottom: 12px; display: inline-block;' />
                    <h1 style='color: #ffffff; margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;'>SISFOPERSKC</h1>
                    <p style='color: #94a3b8; margin: 4px 0 0 0; font-size: 11px; font-weight: 600; letter-spacing: 1px;'>SISTEM INFORMASI PERSONEL KOMPONEN CADANGAN</p>
                </div>

                <!-- BODY CONTENT -->
                <div style='padding: 35px 30px; color: #334155;'>
                    <p style='font-size: 16px; color: #0f172a; margin-top: 0; font-weight: 700;'>
                        Yth. {$this->formattedName},
                    </p>
                    
                    <p style='font-size: 14px; line-height: 1.6; color: #334155;'>
                        Selamat! Akun akses resmi Anda untuk portal kedinasan <strong>SISFOPERSKC (Sistem Informasi Personel Komponen Cadangan)</strong> telah berhasil didaftarkan dan diaktifkan oleh Administrator.
                    </p>

                    <!-- KREDENSIAL BOX -->
                    <div style='background-color: #eff6ff; border: 2px solid #2563eb; border-radius: 12px; padding: 20px; margin: 25px 0;'>
                        <h4 style='margin: 0 0 12px 0; color: #1e40af; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; border-bottom: 1px dashed #bfdbfe; padding-bottom: 8px;'>
                            🔑 Kredensial Akses Masuk Sistem
                        </h4>
                        <table style='width: 100%; border-collapse: collapse; font-size: 13px;'>
                            <tr>
                                <td style='padding: 6px 0; font-weight: bold; color: #1e3a8a; width: 140px;'>NIKC / Username</td>
                                <td style='padding: 6px 0; font-family: monospace; font-weight: bold; color: #0f172a;'>: {$nikc}</td>
                            </tr>
                            <tr>
                                <td style='padding: 6px 0; font-weight: bold; color: #1e3a8a;'>Kata Sandi (Password)</td>
                                <td style='padding: 6px 0; font-family: monospace; font-weight: bold; color: #dc2626;'>: {$this->password}</td>
                            </tr>
                            <tr>
                                <td style='padding: 6px 0; font-weight: bold; color: #1e3a8a;'>Matra TNI</td>
                                <td style='padding: 6px 0; font-weight: bold; color: #0f172a;'>: TNI {$matra}</td>
                            </tr>
                        </table>
                    </div>

                    <div style='background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 14px; border-radius: 6px; font-size: 12px; color: #15803d; margin-bottom: 25px;'>
                        📌 <strong>Lampiran PDF:</strong> Lembar Resmi <em>Surat Informasi Kredensial Akun Personel</em> yang sah telah terlampir pada email ini dalam format dokumen PDF.
                    </div>

                    <!-- TOMBOL MASUK PORTAL -->
                    <div style='text-align: center; margin: 30px 0 20px 0;'>
                        <a href='{$loginUrl}' style='background-color: #2563eb; color: #ffffff; padding: 14px 32px; text-decoration: none; font-size: 14px; font-weight: bold; border-radius: 10px; display: inline-block; box-shadow: 0 4px 10px rgba(37,99,235,0.25);'>
                            Buka Portal Sisfoperskc &rarr;
                        </a>
                    </div>

                    <p style='font-size: 13px; color: #64748b; margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 20px;'>
                        Terima kasih atas perhatian, loyalitas, dan kerja sama Anda.
                    </p>
                </div>

                <!-- FOOTER -->
                <div style='background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b;'>
                    <strong>SISFOPERSKC</strong><br/>
                    <span style='color: #94a3b8; margin-top: 4px; display: inline-block;'>Pesan ini dikirimkan otomatis oleh sistem, mohon tidak membalas email ini secara langsung.</span>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
