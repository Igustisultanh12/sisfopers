<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\WhatsappLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Mengirim pesan WA via Server Node.js Baileys Gateway secara dinamis (Internal Proxy)
     */
    public static function sendMessage($target, $message)
    {
        // 1. Bersihkan nomor (hilangkan spasi, strip, dll)
        $phone = preg_replace('/[^0-9]/', '', $target);

        // 2. Ambil URL Gateway dinamis dari setting database
        $host = Setting::where('key', 'wa_host')->value('value') ?? '127.0.0.1';
        $port = Setting::where('key', 'wa_port')->value('value') ?? '3100';
        $baseUrl = "http://{$host}:{$port}";
        $endpoint = $baseUrl . '/send';

        // Buat log awal dengan status PENDING
        $log = WhatsappLog::create([
            'recipient_number' => $phone,
            'message' => $message,
            'status' => 'PENDING',
        ]);

        // 3. Kirim perintah dengan query parameter terstruktur ke Node.js Baileys engine
        try {
            $response = Http::timeout(15)->get($endpoint, [
                'number' => $phone,
                'msg'    => $message
            ]);

            if ($response->successful()) {
                Log::info("WA Gateway Terkirim via ($endpoint) ke: $phone");
                $log->update(['status' => 'SENT']);
                return true;
            } else {
                Log::error("Server WA ($endpoint) merespon gagal: " . $response->body());
                $log->update([
                    'status' => 'FAILED',
                    'error_response' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Koneksi ke Server WA ($endpoint) Gagal: " . $e->getMessage());
            $log->update([
                'status' => 'FAILED',
                'error_response' => $e->getMessage()
            ]);
            return false;
        }
    }
}
