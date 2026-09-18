<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Mail\SystemNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CustomFormAppAndEmailNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected ?string $url;
    protected string $icon;

    public function __construct(string $title, string $message, ?string $url = null, string $icon = 'form')
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->icon = $icon;
    }

    public function via($notifiable): array
    {
        // Pengiriman Email Resmi Dinas Sisfoperskc (Tanpa WhatsApp)
        $email = $notifiable->email ?? $notifiable->personel?->user?->email ?? null;
        if ($email) {
            try {
                Mail::to($email)->send(
                    new SystemNotificationMail($this->title, $this->message, $notifiable, $this->url)
                );
            } catch (\Throwable $e) {
                Log::error("Gagal mengirimkan email notifikasi formulir ke {$email}: " . $e->getMessage());
            }
        }

        // Notifikasi internal tersimpan pada database aplikasi (In-App)
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'url' => $this->url ?? '#',
        ];
    }
}
