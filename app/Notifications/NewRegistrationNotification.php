<?php

namespace App\Notifications;

use App\Models\Personel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewRegistrationNotification extends Notification
{
    use Queueable;

    protected $personel;

    public function __construct(Personel $personel)
    {
        $this->personel = $personel;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Sisfoperskc] Pendaftaran Baru Perlu Diverifikasi')
            ->greeting('Yth. Administrator / Koordinator,')
            ->line('Anggota baru atas nama ' . $this->personel->full_name . ' (NIKC: ' . ($this->personel->nikc ?? '-') . ') telah mendaftar dan menunggu persetujuan berkas.')
            ->line('Matra/Angkatan: ' . $this->personel->matra . ' / Angkatan ' . $this->personel->angkatan)
            ->action('Tinjau Pendaftaran', route('admin.verification.index'))
            ->line('Silakan login ke portal Sisfoperskc untuk memeriksa kelengkapan berkas pendaftar.')
            ->salutation('Salam, Sisfoperskc');
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'Pendaftaran Baru Perlu Diverifikasi',
            'message' => 'Anggota baru ' . $this->personel->full_name . ' telah mendaftar dan menunggu persetujuan.',
            'icon'    => 'registration',
            'url'     => route('admin.verification.index'),
        ];
    }
}
