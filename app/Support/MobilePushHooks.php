<?php

namespace App\Support;

use App\Jobs\SendMobilePush;
use App\Models\LiveChatMessage;
use App\Models\LiveChatThread;
use App\Models\Pju;
use App\Models\User;
use App\Models\ViconParticipant;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Menghubungkan peristiwa sistem ke notifikasi push aplikasi Android (FCM).
 * Seluruh pengiriman dijalankan setelah respons terkirim dan tidak pernah
 * menggagalkan proses utama.
 */
class MobilePushHooks
{
    public static function register(): void
    {
        // 1. Setiap notifikasi database (pendaftaran, broadcast, verifikasi, formulir, chat, dll.)
        DatabaseNotification::created(function (DatabaseNotification $n) {
            self::safe(function () use ($n) {
                $data = is_array($n->data) ? $n->data : (json_decode((string) $n->data, true) ?: []);
                SendMobilePush::dispatchAfterResponse(
                    [[$n->notifiable_type, (int) $n->notifiable_id]],
                    (string) ($data['title'] ?? 'SISFOPERSKC'),
                    (string) ($data['message'] ?? ''),
                    [
                        'notification_id' => $n->id,
                        'icon' => $data['icon'] ?? 'bell',
                        'url' => self::path($data['url'] ?? null),
                    ],
                    'notification'
                );
            });
        });

        // 2. Pesan Pusat Layanan Informasi (live chat)
        LiveChatMessage::created(function (LiveChatMessage $m) {
            self::safe(function () use ($m) {
                if (in_array(strtoupper((string) $m->sender_type), ['SYSTEM'], true)) {
                    return;
                }
                $thread = LiveChatThread::with('personel.user')->find($m->thread_id);
                if (!$thread) {
                    return;
                }

                $preview = trim((string) $m->message) !== '' ? (string) $m->message : 'Mengirim lampiran';
                $data = ['thread_uuid' => $thread->uuid];

                if (strtoupper((string) $m->sender_type) === 'PERSONEL') {
                    $recipients = self::operatorsFor($thread);
                    $title = 'Pesan baru dari ' . ($m->sender_name ?: ($thread->personel->full_name ?? 'Personel'));
                    $data['url'] = '/admin/live-chat';
                } else {
                    $userId = $thread->personel?->user_id;
                    if (!$userId) {
                        return;
                    }
                    $recipients = [[User::class, (int) $userId]];
                    $title = 'Balasan Pusat Layanan Informasi';
                    $data['url'] = '/personel/live-chat';
                }

                SendMobilePush::dispatchAfterResponse($recipients, $title, mb_substr($preview, 0, 180), $data, 'chat');
            });
        });

        // 3. Undangan rapat dinas (Vicon)
        ViconParticipant::created(function (ViconParticipant $p) {
            self::safe(function () use ($p) {
                if (strtoupper((string) $p->status) !== 'INVITED' || !$p->user_id) {
                    return;
                }
                $room = $p->room;
                SendMobilePush::dispatchAfterResponse(
                    [[User::class, (int) $p->user_id]],
                    'Undangan Rapat Dinas',
                    'Anda diundang ke rapat: ' . ($room->title ?? 'Vicon Dinas'),
                    ['room_uuid' => $room->uuid ?? '', 'room_code' => $room->room_code ?? '', 'url' => '/personel/vicon'],
                    'vicon'
                );
            });
        });
    }

    /**
     * Panggilan video masuk dari Pusat Layanan Informasi (dipanggil oleh LiveChatController::initiateCall).
     */
    public static function incomingCall(LiveChatThread $thread, array $callData, bool $fromPersonel): void
    {
        self::safe(function () use ($thread, $callData, $fromPersonel) {
            $callerName = (string) ($callData['caller']['name'] ?? 'SISFOPERSKC');
            if ($fromPersonel) {
                $recipients = self::operatorsFor($thread);
            } else {
                $userId = $thread->personel?->user_id;
                if (!$userId) {
                    return;
                }
                $recipients = [[User::class, (int) $userId]];
            }

            SendMobilePush::dispatchAfterResponse(
                $recipients,
                'Panggilan video masuk',
                $callerName . ' sedang memanggil Anda',
                [
                    'thread_uuid' => $thread->uuid,
                    'call_id' => (string) ($callData['call_id'] ?? ''),
                    'caller_name' => $callerName,
                    'caller_rank' => (string) ($callData['caller']['pangkat'] ?? ''),
                    'call_type' => (string) ($callData['call_type'] ?? 'video'),
                    'url' => $fromPersonel ? '/admin/live-chat' : '/personel/live-chat',
                ],
                'call'
            );
        });
    }

    /**
     * Admin dan koordinator yang berwenang atas personel pada thread.
     *
     * @return array<int, array{0: string, 1: int}>
     */
    protected static function operatorsFor(LiveChatThread $thread): array
    {
        $personel = $thread->personel;
        $users = User::with(['role', 'personel'])
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['admin', 'kordinator_matra', 'kordinator_angkatan']))
            ->where('is_active', true)
            ->get()
            ->filter(function (User $u) use ($personel) {
                $role = $u->role?->name;
                if ($role === 'admin' || !$personel) {
                    return true;
                }
                if ($role === 'kordinator_matra') {
                    return $u->personel && $u->personel->matra === $personel->matra;
                }
                if ($role === 'kordinator_angkatan') {
                    return $u->personel && (string) $u->personel->angkatan === (string) $personel->angkatan;
                }
                return false;
            });

        return $users->map(fn (User $u) => [User::class, (int) $u->id])->values()->all();
    }

    protected static function path(?string $url): string
    {
        if (!$url || $url === '#') {
            return '';
        }
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $query = parse_url($url, PHP_URL_QUERY);
        return $path . ($query ? '?' . $query : '');
    }

    protected static function safe(callable $fn): void
    {
        try {
            if (!\App\Services\FcmService::enabled()) {
                return;
            }
            $fn();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
