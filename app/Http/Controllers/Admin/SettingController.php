<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return Inertia::render('Admin/Setting/Index', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:50',
            'wa_host' => 'required|string',
            'wa_port' => 'required|numeric',
            'wa_api_key' => 'nullable|string',
            'wa_session' => 'required|string',
            'login_background' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,webp,ico|max:2048',
            'logo_tni' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'logo_ad' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'logo_al' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'logo_au' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'disable_whatsapp_otp' => 'nullable',
            'under_maintenance' => 'nullable',
            'coturn_host' => 'nullable|string|max:100',
            'coturn_port' => 'nullable|numeric',
            'coturn_secret' => 'nullable|string|max:100',
            'metered_app_name' => 'nullable|string|max:100',
            'metered_api_key' => 'nullable|string|max:200',
            'agora_app_id' => 'nullable|string|max:100',
            'agora_app_certificate' => 'nullable|string|max:100',
            'turnstile_enabled' => 'nullable',
            'turnstile_site_key' => 'nullable|string|max:200',
            'turnstile_secret_key' => 'nullable|string|max:200',
        ]);

        foreach ($validated as $key => $value) {
            if ($key === 'wa_api_key' && empty($value)) {
                continue; // Pertahankan token lama jika input dikosongkan
            }
            
            if (in_array($key, ['login_background', 'favicon', 'logo_tni', 'logo_ad', 'logo_al', 'logo_au'])) {
                if ($request->hasFile($key)) {
                    $path = $request->file($key)->store('settings', 'public');
                    Setting::updateOrCreate(['key' => $key], ['value' => '/storage/' . $path]);
                }
            } elseif ($key === 'disable_whatsapp_otp') {
                $isDisabled = ($value === '1' || $value === 1 || $value === 'true' || $value === true || $request->input('disable_whatsapp_otp') === '1');
                Setting::updateOrCreate(['key' => 'disable_whatsapp_otp'], ['value' => $isDisabled ? '1' : '0']);
            } elseif ($key === 'under_maintenance') {
                $isMaint = ($value === '1' || $value === 1 || $value === 'true' || $value === true || $request->input('under_maintenance') === '1');
                Setting::updateOrCreate(['key' => 'under_maintenance'], ['value' => $isMaint ? '1' : '0']);
            } else {
                Setting::updateOrCreate(['key' => $key], ['value' => (string)($value ?? '0')]);
            }
        }

        // Tentukan secara eksplisit status sakelar disable_whatsapp_otp
        $isDisabled = $request->has('disable_whatsapp_otp') && in_array((string)$request->input('disable_whatsapp_otp'), ['1', 'true']);
        Setting::updateOrCreate(
            ['key' => 'disable_whatsapp_otp'],
            ['value' => $isDisabled ? '1' : '0']
        );

        // Tentukan secara eksplisit status sakelar under_maintenance
        $isMaint = $request->has('under_maintenance') && in_array((string)$request->input('under_maintenance'), ['1', 'true']);
        Setting::updateOrCreate(
            ['key' => 'under_maintenance'],
            ['value' => $isMaint ? '1' : '0']
        );

        // Tentukan secara eksplisit konfigurasi Cloudflare Turnstile
        $turnstileEnabled = $request->has('turnstile_enabled') && in_array((string)$request->input('turnstile_enabled'), ['1', 'true']);
        Setting::updateOrCreate(
            ['key' => 'turnstile_enabled'],
            ['value' => $turnstileEnabled ? '1' : '0']
        );

        if ($request->has('turnstile_site_key')) {
            Setting::updateOrCreate(
                ['key' => 'turnstile_site_key'],
                ['value' => trim((string)$request->input('turnstile_site_key', ''))]
            );
        }

        if ($request->has('turnstile_secret_key')) {
            Setting::updateOrCreate(
                ['key' => 'turnstile_secret_key'],
                ['value' => trim((string)$request->input('turnstile_secret_key', ''))]
            );
        }

        return redirect()->route('admin.setting.index')->with('success', 'Konfigurasi parameter sistem berhasil disinkronisasi.');
    }

    public function testWaConnection()
    {
        $host = Setting::where('key', 'wa_host')->value('value') ?? '127.0.0.1';
        $port = Setting::where('key', 'wa_port')->value('value') ?? '3100';
        $session = Setting::where('key', 'wa_session')->value('value') ?? 'default';
        
        $apiKeyObj = Setting::where('key', 'wa_api_key')->first();
        $apiKey = $apiKeyObj ? $apiKeyObj->value : '';

        try {
            $response = Http::timeout(5)->get("http://{$host}:{$port}/status");

            if ($response->successful()) {
                $nodeData = $response->json();
                $gatewayStatus = $nodeData['status'] ?? 'OFFLINE';
                if ($gatewayStatus === 'CONNECTED' || $gatewayStatus === 'READY' || $gatewayStatus === 'ONLINE') {
                    return response()->json(['status' => true, 'message' => 'Koneksi ke Local WA Gateway Port ' . $port . ' Terhubung Aktif (CONNECTED).']);
                }
                return response()->json(['status' => false, 'message' => 'Gateway merespon namun status sesi: ' . $gatewayStatus]);
            }
            return response()->json(['status' => false, 'message' => 'Gagal membaca status dari service gateway.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Gagal terhubung ke service gateway lokal. Periksa status aplikasi node.js Anda.']);
        }
    }

    public function testSendWa(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20'
        ]);

        $phone = $request->phone;
        $message = "[PEMBERITAHUAN] *SISFOPERSKC: UJI COBA GATEWAY*\n\nHalo, ini adalah pesan uji coba pengiriman dari panel Pengaturan Sistem SISFOPERSKC Anda. Koneksi WhatsApp Gateway berhasil dikonfigurasi dengan aman!";

        $success = \App\Services\WhatsappService::sendMessage($phone, $message);

        if ($success) {
            return response()->json([
                'status' => true,
                'message' => 'Pesan uji coba berhasil terkirim ke nomor ' . $phone
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal mengirim pesan uji coba. Periksa log gateway Anda.'
        ]);
    }

    /**
     * Konfigurasi Notifikasi Push Aplikasi Android (Firebase Cloud Messaging).
     * - service_account : berkas JSON service account Firebase (dipakai server untuk mengirim push)
     * - google_services : berkas google-services.json (konfigurasi klien yang dibaca aplikasi saat dibuka)
     */
    public function updateFcm(Request $request)
    {
        $request->validate([
            'fcm_enabled' => 'nullable',
            'service_account' => 'nullable|file|max:256',
            'google_services' => 'nullable|file|max:256',
        ]);

        $enabled = in_array((string) $request->input('fcm_enabled', '0'), ['1', 'true'], true);
        Setting::updateOrCreate(['key' => 'fcm_enabled'], ['value' => $enabled ? '1' : '0']);

        if ($request->hasFile('service_account')) {
            $json = json_decode((string) file_get_contents($request->file('service_account')->getRealPath()), true);
            if (!is_array($json) || ($json['type'] ?? '') !== 'service_account' || empty($json['private_key']) || empty($json['client_email']) || empty($json['project_id'])) {
                return response()->json(['success' => false, 'message' => 'Berkas service account tidak valid. Unduh dari Firebase Console › Project settings › Service accounts › Generate new private key.'], 422);
            }
            $path = 'firebase/service-account.json';
            \Illuminate\Support\Facades\Storage::disk('private')->put($path, json_encode($json));
            Setting::updateOrCreate(['key' => 'fcm_service_account_path'], ['value' => $path]);
            Setting::updateOrCreate(['key' => 'fcm_client_email'], ['value' => $json['client_email']]);
            Setting::updateOrCreate(['key' => 'fcm_server_project_id'], ['value' => $json['project_id']]);
        }

        if ($request->hasFile('google_services')) {
            $json = json_decode((string) file_get_contents($request->file('google_services')->getRealPath()), true);
            $projectInfo = $json['project_info'] ?? null;
            $clients = $json['client'] ?? [];
            if (!is_array($projectInfo) || empty($clients)) {
                return response()->json(['success' => false, 'message' => 'Berkas google-services.json tidak valid.'], 422);
            }
            $package = config('services.fcm.android_package', 'id.my.sisfoperskc.app');
            $client = collect($clients)->first(fn ($c) => ($c['client_info']['android_client_info']['package_name'] ?? '') === $package) ?? $clients[0];
            $appId = $client['client_info']['mobilesdk_app_id'] ?? null;
            $apiKey = $client['api_key'][0]['current_key'] ?? null;
            if (!$appId || !$apiKey) {
                return response()->json(['success' => false, 'message' => 'google-services.json tidak memuat App ID / API key Android.'], 422);
            }
            Setting::updateOrCreate(['key' => 'fcm_project_id'], ['value' => $projectInfo['project_id'] ?? '']);
            Setting::updateOrCreate(['key' => 'fcm_sender_id'], ['value' => (string) ($projectInfo['project_number'] ?? '')]);
            Setting::updateOrCreate(['key' => 'fcm_android_app_id'], ['value' => $appId]);
            Setting::updateOrCreate(['key' => 'fcm_api_key'], ['value' => $apiKey]);
            Setting::updateOrCreate(['key' => 'fcm_storage_bucket'], ['value' => $projectInfo['storage_bucket'] ?? '']);
            Setting::updateOrCreate(['key' => 'fcm_package_name'], ['value' => $client['client_info']['android_client_info']['package_name'] ?? '']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi notifikasi push aplikasi berhasil disimpan.',
            'status' => \App\Services\FcmService::status(),
        ]);
    }

    public function fcmStatus()
    {
        return response()->json(['success' => true, 'status' => \App\Services\FcmService::status()]);
    }

    public function testFcm(Request $request)
    {
        if (!\App\Services\FcmService::enabled()) {
            return response()->json(['success' => false, 'message' => 'FCM belum aktif. Aktifkan sakelar dan unggah berkas service account terlebih dahulu.'], 422);
        }

        $user = auth()->user();
        $devices = \App\Models\MobileDevice::where('tokenable_type', get_class($user))->where('tokenable_id', $user->id)->count();
        if ($devices === 0) {
            return response()->json(['success' => false, 'message' => 'Belum ada perangkat Android yang terhubung ke akun admin ini. Login di aplikasi Android terlebih dahulu, lalu coba lagi.'], 422);
        }

        $result = \App\Services\FcmService::sendToNotifiable(get_class($user), $user->id, 'Uji Notifikasi SISFOPERSKC', 'Notifikasi push aplikasi Android berhasil dikonfigurasi.', ['url' => '/notifications'], 'notification');

        return response()->json([
            'success' => $result['sent'] > 0,
            'message' => $result['sent'] > 0
                ? "Notifikasi uji terkirim ke {$result['sent']} perangkat."
                : ('Gagal mengirim notifikasi uji. ' . ($result['error'] ?? 'Periksa berkas service account.')),
        ], $result['sent'] > 0 ? 200 : 422);
    }

    public function deleteFcm()
    {
        $path = Setting::where('key', 'fcm_service_account_path')->value('value');
        if ($path) {
            \Illuminate\Support\Facades\Storage::disk('private')->delete($path);
        }
        Setting::whereIn('key', ['fcm_enabled', 'fcm_service_account_path', 'fcm_client_email', 'fcm_server_project_id', 'fcm_project_id', 'fcm_sender_id', 'fcm_android_app_id', 'fcm_api_key', 'fcm_storage_bucket', 'fcm_package_name'])->delete();
        \Illuminate\Support\Facades\Cache::forget('fcm:access_token');

        return response()->json(['success' => true, 'message' => 'Konfigurasi FCM dihapus.', 'status' => \App\Services\FcmService::status()]);
    }

    /**
     * Mendeteksi alamat IP publik peladen secara otomatis
     */
    public function detectPublicIp()
    {
        try {
            $response = Http::timeout(3)->get('https://api.ipify.org?format=json');
            if ($response->successful() && !empty($response->json('ip'))) {
                return response()->json(['success' => true, 'ip' => $response->json('ip')]);
            }
        } catch (\Throwable $e) {}

        try {
            $ip = trim(Http::timeout(3)->get('https://ifconfig.me/ip')->body());
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return response()->json(['success' => true, 'ip' => $ip]);
            }
        } catch (\Throwable $e) {}

        return response()->json(['success' => false, 'message' => 'Gagal mendeteksi IP publik otomatis dari peladen.']);
    }
}