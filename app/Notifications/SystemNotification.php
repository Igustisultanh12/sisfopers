<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Mail\SystemNotificationMail;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Mail;

class SystemNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $message;
    protected $icon;
    protected $url;

    public function __construct(string $title, string $message, string $icon = '🔔', ?string $url = null)
    {
        $this->title   = $title;
        $this->message = $message;
        $this->icon    = $icon;
        $this->url     = $url;
    }

    public function via($notifiable): array
    {
        // Pemicu Notifikasi WA otomatis jika user memiliki personel & phone_number
        if (isset($notifiable->personel) && $notifiable->personel->phone_number) {
            $waMsg = "🔔 *{$this->title}*\n\n{$this->message}";
            if ($this->url && $this->url !== '#') {
                $waMsg .= "\n\nTautan: " . $this->url;
            }
            try {
                WhatsappService::sendMessage($notifiable->personel->phone_number, $waMsg);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal kirim WA notifikasi system: " . $e->getMessage());
            }
        }

        // Kirim Email Mailable dengan Desain Resmi Sisfoperskc (Logo Pengaturan & Yth. Pangkat KC Nama)
        $email = $notifiable->email ?? $notifiable->personel?->user?->email;
        if ($email) {
            try {
                Mail::to($email)->send(
                    new SystemNotificationMail($this->title, $this->message, $notifiable, $this->url)
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal kirim Email notification system ke {$email}: " . $e->getMessage());
            }
        }

        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'icon'    => $this->icon,
            'url'     => $this->url ?? '#',
        ];
    }
}
