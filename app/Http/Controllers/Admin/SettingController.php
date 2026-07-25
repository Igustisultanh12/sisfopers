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
            'disable_whatsapp_otp' => 'nullable'
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
        $message = "📢 *SISFOPERSKC: UJI COBA GATEWAY*\n\nHalo, ini adalah pesan uji coba pengiriman dari panel Pengaturan Sistem SISFOPERSKC Anda. Koneksi WhatsApp Gateway berhasil dikonfigurasi dengan aman!";

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
}