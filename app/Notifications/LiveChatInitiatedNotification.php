<?php

namespace App\Notifications;

use App\Mail\SystemNotificationMail;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LiveChatInitiatedNotification extends Notification
{
    use Queueable;

    protected string $senderName;
    protected string $title;
    protected ?string $initialMessage;
    protected string $chatUrl;

    public function __construct(string $senderName, ?string $initialMessage = null, ?string $chatUrl = null)
    {
        $this->senderName = $senderName;
        $this->title = 'Pusat Layanan Informasi: Sesi Chat Baru Dibuka';
        $this->initialMessage = $initialMessage ? trim($initialMessage) : null;
        $this->chatUrl = $chatUrl ?: route('personel.chat.index');
    }

    /**
     * Kanal pengiriman notifikasi
     */
    public function via($notifiable): array
    {
        $personel = $notifiable->personel ?? (is_a($notifiable, \App\Models\Personel::class) ? $notifiable : null);
        $user = $notifiable->user ?? (is_a($notifiable, \App\Models\User::class) ? $notifiable : null);

        // 1. Notifikasi WhatsApp Resmi Dinas
        $phoneNumber = $personel?->phone_number ?? $user?->phone_number ?? null;
        if ($phoneNumber) {
            $namaPersonel = $personel ? trim($personel->full_name) : 'Personel Komcad';
            $pangkatPersonel = $personel ? \App\Models\Personel::formatLongRank($personel->pangkat) : 'Prajurit Komcad';

            $waMsg = "*Pusat Layanan Informasi Personel Komcad*\n\n";
            $waMsg .= "Yth. {$pangkatPersonel} {$namaPersonel},\n\n";
            $waMsg .= "Petugas dinas ({$this->senderName}) telah memulai sesi obrolan dinas Pusat Layanan Informasi dengan Anda melalui portal SISFOPERS KC.\n";

            if ($this->initialMessage) {
                $waMsg .= "\nPesan Pembuka:\n\"{$this->initialMessage}\"\n";
            }

            $waMsg .= "\nSilakan buka aplikasi SISFOPERS KC atau akses tautan berikut untuk membaca dan membalas pesan:\n";
            $waMsg .= "{$this->chatUrl}\n\n";
            $waMsg .= "Komando Pembina Komponen Cadangan";

            try {
                WhatsappService::sendMessage($phoneNumber, $waMsg);
            } catch (\Throwable $e) {
                Log::error("Gagal mengirimkan notifikasi WA inisiasi chat ke {$phoneNumber}: " . $e->getMessage());
            }
        }

        // 2. Notifikasi Email Dinas Resmi
        $email = $user?->email ?? $personel?->email ?? $notifiable->email ?? null;
        if ($email) {
            $emailBody = "Petugas dinas ({$this->senderName}) telah membuka sesi percakapan Pusat Layanan Informasi dengan Anda.";
            if ($this->initialMessage) {
                $emailBody .= "\n\nPesan Pembuka:\n\"{$this->initialMessage}\"";
            }
            $emailBody .= "\n\nSilakan klik tombol di bawah ini atau buka aplikasi SISFOPERS KC Anda untuk merespons percakapan dinas ini.";

            try {
                Mail::to($email)->send(
                    new SystemNotificationMail($this->title, $emailBody, $personel ?: $notifiable, $this->chatUrl)
                );
            } catch (\Throwable $e) {
                Log::error("Gagal mengirimkan email notifikasi inisiasi chat ke {$email}: " . $e->getMessage());
            }
        }

        // 3. Notifikasi Database (Aplikasi SISFOPERS KC & Bel Notifikasi Web)
        return ['database'];
    }

    /**
     * Format payload yang tersimpan pada tabel notifications untuk Aplikasi SISFOPERS KC
     */
    public function toArray($notifiable): array
    {
        $desc = "Sesi obrolan dinas telah dimulai oleh {$this->senderName}.";
        if ($this->initialMessage) {
            $desc .= " Pesan: \"{$this->initialMessage}\"";
        }

        return [
            'title' => $this->title,
            'message' => $desc,
            'icon' => 'chat',
            'url' => $this->chatUrl,
        ];
    }
}
