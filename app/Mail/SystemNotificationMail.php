<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\Personel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public string $messageText;
    public string $formattedName;
    public ?string $url;
    public string $logoUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $title, string $messageText, $userOrPersonel = null, ?string $url = null)
    {
        $this->title       = $title;
        $this->messageText = $messageText;
        $this->url         = $url ?? route('login');

        // Formating Yth. Pangkat + KC + Nama Lengkap
        $this->formattedName = $this->formatMilitaryTitle($userOrPersonel);

        // Ambil logo TNI/Sisfoperskc dari Pengaturan Admin
        $savedLogo = Setting::where('key', 'logo_tni')->value('value');
        if ($savedLogo) {
            $this->logoUrl = str_starts_with($savedLogo, 'http') ? $savedLogo : url($savedLogo);
        } else {
            $this->logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/b/b5/Tentara_Nasional_Indonesia_insignia.svg';
        }
    }

    /**
     * Helper untuk memformat nama: "Prada KC Amrih Abi Maarif"
     */
    private function formatMilitaryTitle($userOrPersonel): string
    {
        $personel = null;

        if ($userOrPersonel instanceof Personel) {
            $personel = $userOrPersonel;
        } elseif (is_object($userOrPersonel) && isset($userOrPersonel->personel)) {
            $personel = $userOrPersonel->personel;
        }

        if (!$personel) {
            $rawName = rtrim(is_object($userOrPersonel) ? ($userOrPersonel->name ?? $userOrPersonel->username ?? 'Prajurit') : 'Prajurit', ',.');
            return 'Prajurit KC ' . $rawName;
        }

        $fullName = rtrim(trim($personel->full_name ?? 'Prajurit'), ',.');
        $pangkat  = trim($personel->pangkat ?? '');

        if (empty($pangkat)) {
            return 'Prajurit KC ' . $fullName;
        }

        // Jika pangkat sudah memuat "KC", gabungkan langsung
        if (str_contains(strtoupper($pangkat), 'KC')) {
            return $pangkat . ' ' . $fullName;
        }

        // Format standar: Pangkat + KC + Nama (contoh: "Letda KC I Gusti Sultan")
        return $pangkat . ' KC ' . $fullName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('[Sisfoperskc] ' . $this->title)
                    ->html($this->htmlContent());
    }

    /**
     * Generates Official Sisfoperskc HTML Email Template
     */
    private function htmlContent(): string
    {
        $buttonHtml = '';
        if ($this->url && $this->url !== '#') {
            $buttonHtml = "
            <div style='text-align: center; margin: 30px 0 20px 0;'>
                <a href='{$this->url}' style='background-color: #2563eb; color: #ffffff; padding: 14px 32px; text-decoration: none; font-size: 14px; font-weight: bold; border-radius: 10px; display: inline-block; box-shadow: 0 4px 10px rgba(37,99,235,0.25);'>
                    Buka Sisfoperskc &rarr;
                </a>
            </div>";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>{$this->title}</title>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 25px 15px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;'>
                
                <!-- HEADER WITH LOGO FROM ADMIN SETTINGS -->
                <div style='background-color: #0f172a; padding: 28px 24px; text-align: center; border-bottom: 4px solid #2563eb;'>
                    <img src='{$this->logoUrl}' alt='Logo Sisfoperskc' style='height: 56px; width: auto; max-width: 160px; object-contain: fit; margin-bottom: 12px; display: inline-block;' />
                    <h1 style='color: #ffffff; margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;'>SISFOPERSKC</h1>
                    <p style='color: #94a3b8; margin: 4px 0 0 0; font-size: 11px; font-weight: 600; letter-spacing: 1px;'>SISTEM INFORMASI PERSONEL KOMPONEN CADANGAN</p>
                </div>

                <!-- CONTENT BODY -->
                <div style='padding: 35px 30px; color: #334155;'>
                    <p style='font-size: 16px; color: #0f172a; margin-top: 0; font-weight: 700;'>
                        Yth. {$this->formattedName},
                    </p>
                    
                    <div style='font-size: 14px; line-height: 1.6; color: #334155; margin-top: 16px; background-color: #f8fafc; padding: 20px; border-left: 4px solid #2563eb; border-radius: 8px;'>
                        {$this->messageText}
                    </div>

                    {$buttonHtml}

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
