<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QrCodeService
{
    /**
     * Generate Base64 Data URI PNG QR Code untuk DomPDF
     */
    public static function generateBase64(string $url): string
    {
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=' . urlencode($url);

        try {
            $response = Http::timeout(3)->get($qrApiUrl);
            if ($response->successful()) {
                return 'data:image/png;base64,' . base64_encode($response->body());
            }
        } catch (\Exception $e) {
            Log::warning('QR Server API unreachable, using direct stream fallback: ' . $e->getMessage());
        }

        try {
            $content = @file_get_contents($qrApiUrl);
            if ($content) {
                return 'data:image/png;base64,' . base64_encode($content);
            }
        } catch (\Exception $e) {
            // Silence
        }

        // Generic fallback 1x1 base64 png
        return 'data:image/png;base64,iVBORw0KGgoAAAANAA5ErkJggg==';
    }
}
