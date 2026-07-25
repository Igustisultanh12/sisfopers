<?php

namespace App\Notifications;

use App\Models\Broadcast;
use App\Mail\SystemNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class NewBroadcastNotification extends Notification
{
    use Queueable;

    protected $broadcast;

    public function __construct(Broadcast $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    public function via($notifiable): array
    {
        $url  = route('personel.broadcast.show', $this->broadcast->uuid);
        $msg  = "Telah diterbitkan Perintah / Maklumat Kegiatan Baru pada portal Sisfoperskc:<br/><br/>"
              . "• <strong>Judul Kegiatan:</strong> {$this->broadcast->title}<br/>"
              . "• <strong>Kategori:</strong> " . strtoupper($this->broadcast->category) . "<br/>"
              . "• <strong>Waktu Pelaksanaan:</strong> " . date('d-m-Y', strtotime($this->broadcast->event_date)) . " pukul {$this->broadcast->event_time} WIB<br/>"
              . "• <strong>Lokasi:</strong> {$this->broadcast->location}<br/>"
              . "• <strong>Deskripsi:</strong> {$this->broadcast->description}<br/>"
              . "• <strong>Batas Waktu Konfirmasi:</strong> " . date('d-m-Y H:i', strtotime($this->broadcast->deadline)) . " WIB<br/><br/>"
              . "Harap segera melakukan konfirmasi kesiapan Anda sebelum batas waktu yang ditentukan.";

        // Kirim Email Mailable dengan Desain Resmi Sisfoperskc (Logo Pengaturan & Yth. Pangkat KC Nama)
        $email = $notifiable->email ?? $notifiable->personel?->user?->email;
        if ($email) {
            try {
                Mail::to($email)->send(
                    new SystemNotificationMail(
                        'Broadcast Perintah Kegiatan: ' . $this->broadcast->title,
                        $msg,
                        $notifiable,
                        $url
                    )
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal kirim email broadcast ke {$email}: " . $e->getMessage());
            }
        }

        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'Instruksi Komando Baru',
            'message' => 'Telah diterbitkan perintah kegiatan baru: ' . $this->broadcast->title,
            'icon'    => 'broadcast',
            'url'     => route('personel.broadcast.show', $this->broadcast->uuid),
        ];
    }
}