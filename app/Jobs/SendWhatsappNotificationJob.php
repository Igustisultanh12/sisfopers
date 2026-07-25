<?php

namespace App\Jobs;

use App\Models\WhatsappLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SendWhatsappNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $message;

    public function __construct(string $phone, string $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle(): void
    {
        \App\Services\WhatsappService::sendMessage($this->phone, $this->message);
    }
}