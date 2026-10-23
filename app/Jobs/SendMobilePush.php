<?php

namespace App\Jobs;

use App\Services\FcmService;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Pengiriman push FCM ke aplikasi Android.
 * Dijalankan setelah respons HTTP terkirim (dispatchAfterResponse) agar tidak memperlambat pengguna.
 */
class SendMobilePush
{
    use Dispatchable;

    /**
     * @param array<int, array{0: string, 1: int}> $recipients pasangan [notifiable_type, notifiable_id]
     */
    public function __construct(
        public array $recipients,
        public string $title,
        public string $body,
        public array $data = [],
        public string $type = 'notification',
    ) {
    }

    public function handle(): void
    {
        if (!FcmService::enabled()) {
            return;
        }

        $seen = [];
        foreach ($this->recipients as [$type, $id]) {
            $key = $type . ':' . $id;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            FcmService::sendToNotifiable($type, (int) $id, $this->title, $this->body, $this->data, $this->type);
        }
    }
}
