<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\Personel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $nikc;
    public string $otpCode;
    public string $type;
    public string $expiredInfo;
    public string $logoUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name, string $nikc, string $otpCode, string $type = 'Reset Password', string $expiredInfo = '24 Jam')
    {
        $this->name        = $name;
        $this->nikc        = $nikc;
        $this->otpCode     = $otpCode;
        $this->type        = $type;
        $this->expiredInfo = $expiredInfo;

        // Ambil logo TNI/Sisfoperskc dari Pengaturan Admin
        $savedLogo = Setting::where('key', 'logo_tni')->value('value');
        if ($savedLogo) {
            $this->logoUrl = str_starts_with($savedLogo, 'http') ? $savedLogo : url($savedLogo);
        } else {
            $this->logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/b/b5/Tentara_Nasional_Indonesia_insignia.svg';
        }
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('[Sisfoperskc] Kode OTP ' . $this->type)
                    ->html($this->htmlContent());
    }

    /**
     * Format nama dengan Pangkat + KC + Nama jika memungkinkan
     */
    private function getFormattedGreeting(): string
    {
        $fullName = rtrim(trim($this->name), ',.');
        
        // Cari data personel berdasarkan NIKC jika tersedia
        $personel = Personel::where('nikc', $this->nikc)->first();
        if ($personel && $personel->pangkat) {
            $pangkat = trim($personel->pangkat);
            $cleanName = rtrim(trim($personel->full_name), ',.');
            if (str_contains(strtoupper($pangkat), 'KC')) {
                return 'Yth. ' . $pangkat . ' ' . $cleanName;
            }
            return 'Yth. ' . $pangkat . ' KC ' . $cleanName;
        }

        if (!str_contains(strtoupper($fullName), 'KC')) {
            return 'Yth. Prajurit KC ' . $fullName;
        }

        return 'Yth. ' . $fullName;
    }

    /**
     * Generates HTML Email Content for Gmail & Mail clients
     */
    private function htmlContent(): string
    {
        $greeting = $this->getFormattedGreeting();

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Kode OTP Sisfoperskc</title>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 25px 15px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;'>
                
                <!-- HEADER WITH LOGO FROM ADMIN SETTINGS -->
                <div style='background-color: #0f172a; padding: 28px 24px; text-align: center; border-bottom: 4px solid #2563eb;'>
                    <img src='{$this->logoUrl}' alt='Logo Sisfoperskc' style='height: 56px; width: auto; max-width: 160px; object-contain: fit; margin-bottom: 12px; display: inline-block;' />
                    <h1 style='color: #ffffff; margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;'>SISFOPERSKC</h1>
                    <p style='color: #94a3b8; margin: 4px 0 0 0; font-size: 11px; font-weight: 600; letter-spacing: 1px;'>SISTEM INFORMASI PERSONEL KOMPONEN CADANGAN</p>
                </div>

                <!-- CONTENT -->
                <div style='padding: 35px 30px; color: #334155;'>
                    <p style='font-size: 16px; color: #0f172a; margin-top: 0; font-weight: 700;'>
                        {$greeting},
                    </p>
                    
                    <p style='font-size: 14px; line-height: 1.5; color: #334155;'>
                        Berikut adalah Kode OTP (One-Time Password) Anda untuk verifikasi <strong>{$this->type}</strong> pada Sisfoperskc:
                    </p>

                    <!-- OTP BOX -->
                    <div style='background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); color: #ffffff; padding: 24px; text-align: center; border-radius: 12px; margin: 25px 0; box-shadow: 0 4px 12px rgba(30,58,138,0.25);'>
                        <span style='display: block; font-size: 12px; color: #93c5fd; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px;'>Kode OTP Verifikasi</span>
                        <span style='font-size: 34px; font-weight: bold; letter-spacing: 10px; font-family: monospace;'>{$this->otpCode}</span>
                        <span style='display: block; font-size: 11px; color: #cbd5e1; margin-top: 8px;'>Berlaku Selama: {$this->expiredInfo}</span>
                    </div>

                    <!-- DETAILS TABLE -->
                    <table style='width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 25px; background-color: #f8fafc; border-radius: 8px; padding: 12px;'>
                        <tr>
                            <td style='padding: 10px 12px; color: #64748b; width: 130px; font-weight: 600;'>Nama Personel</td>
                            <td style='padding: 10px 12px; font-weight: bold; color: #0f172a;'>: {$this->name}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 12px; color: #64748b; font-weight: 600;'>17-Digit NIKC</td>
                            <td style='padding: 10px 12px; font-weight: bold; color: #0f172a;'>: {$this->nikc}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 12px; color: #64748b; font-weight: 600;'>Peruntukan</td>
                            <td style='padding: 10px 12px; font-weight: bold; color: #2563eb;'>: Otentikasi {$this->type}</td>
                        </tr>
                    </table>

                    <div style='background-color: #fffbe6; border-left: 4px solid #f59e0b; padding: 14px; border-radius: 6px; font-size: 12px; color: #92400e; margin-bottom: 20px;'>
                        <strong>PERINGATAN KEAMANAN:</strong> Jangan pernah memberikan kode OTP ini kepada siapa pun. Tim Sisfoperskc tidak pernah meminta Kode OTP Anda.
                    </div>

                    <p style='font-size: 13px; color: #64748b; margin-top: 25px; border-top: 1px solid #f1f5f9; padding-top: 15px;'>
                        Apabila Anda tidak merasa melakukan permintaan ini, silakan abaikan pesan ini.
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
