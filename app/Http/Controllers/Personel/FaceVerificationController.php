<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\AuditLog;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class FaceVerificationController extends Controller
{
    /**
     * Menampilkan Antarmuka Verifikasi Akun via OTP WhatsApp
     */
    public function index()
    {
        $user = Auth::user();

        // Antisipasi: jika sudah verified, jangan biarkan masuk gerbang verifikasi lagi
        if ($user->personel && $user->personel->face_verified) {
            return redirect()->route('personel.sinyalmen.create');
        }

        $phoneNumber = $user->personel ? $user->personel->phone_number : '';
        $length = strlen($phoneNumber);
        if ($length > 6) {
            $maskedPhone = substr($phoneNumber, 0, 4) . str_repeat('*', $length - 8) . substr($phoneNumber, -4);
        } else {
            $maskedPhone = $phoneNumber;
        }

        $disableWhatsappOtp = \App\Models\Setting::where('key', 'disable_whatsapp_otp')->value('value') ?? '0';

        return Inertia::render('Personel/FaceVerification', [
            'masked_phone' => $maskedPhone,
            'disable_whatsapp_otp' => in_array((string)$disableWhatsappOtp, ['1', 'true']),
        ]);
    }

    /**
     * Mengirimkan kode OTP ke WhatsApp Personel
     */
    public function requestOtp(Request $request)
    {
        $user = Auth::user();
        $personel = $user->personel;

        if (!$personel) {
            return response()->json([
                'success' => false,
                'message' => 'Profil personel tidak ditemukan.'
            ], 404);
        }

        // Cek apakah request OTP WhatsApp dinonaktifkan oleh admin
        $disableWhatsappOtp = \App\Models\Setting::where('key', 'disable_whatsapp_otp')->value('value') ?? '0';
        $disableWa = in_array((string)$disableWhatsappOtp, ['1', 'true']);

        // Proteksi Blokir Percobaan
        $lockKey = "otp_lock_{$personel->id}";
        if (Cache::has($lockKey)) {
            $secondsLeft = Cache::ttl($lockKey);
            return response()->json([
                'success'      => false,
                'locked'       => true,
                'seconds_left' => $secondsLeft,
                'message'      => "Batas pengiriman OTP terlampaui. Harap tunggu {$secondsLeft} detik (5 menit) sebelum meminta OTP kembali.",
            ], 429);
        }

        // Proteksi Cooldown: 60 detik
        $lastSentAt = session()->get('account_verification_otp_sent_at');
        if ($lastSentAt && now()->diffInSeconds($lastSentAt) < 60) {
            $secondsLeft = 60 - now()->diffInSeconds($lastSentAt);
            return response()->json([
                'success' => false,
                'message' => "Harap tunggu {$secondsLeft} detik sebelum meminta OTP kembali.",
            ], 429);
        }

        // Proteksi Rate Limiting: max 3x dalam 5 menit, jika lebih blokir 5 menit
        $otpRequestCountKey = "otp_request_count_{$personel->id}";
        $requestCount = Cache::get($otpRequestCountKey, 0);

        if ($requestCount >= 3) {
            Cache::put($lockKey, true, 300); // blokir 5 menit
            Cache::forget($otpRequestCountKey); // reset count
            return response()->json([
                'success' => false,
                'locked'  => true,
                'seconds_left' => 300,
                'message' => 'Batas pengiriman OTP terlampaui. Akun Anda diblokir dari pengiriman OTP selama 5 menit.',
            ], 429);
        }

        $otpCode = strval(rand(100000, 999999));
        $expiredAt = now()->addMinutes(10);

        // Simpan OTP ke session
        session()->put('account_verification_otp', [
            'code' => $otpCode,
            'expires_at' => $expiredAt,
        ]);
        session()->put('account_verification_otp_sent_at', now());

        $sentWa = false;
        if (!$disableWa) {
            $message = "SISFOPERSKC: Kode OTP untuk verifikasi akun Anda adalah *{$otpCode}*. Kode ini bersifat rahasia dan berlaku selama 10 menit.";
            $sentWa = WhatsappService::sendMessage($personel->phone_number, $message);
        }

        // Kirim Kode OTP via Email (Gmail SMTP)
        $sentEmail = false;
        $userEmail = $user->email;
        if ($userEmail) {
            try {
                \Illuminate\Support\Facades\Mail::to($userEmail)->send(
                    new \App\Mail\OtpNotificationMail(
                        $personel->full_name,
                        $personel->nikc ?? '-',
                        $otpCode,
                        'Verifikasi Akun',
                        '10 Menit'
                    )
                );
                $sentEmail = true;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal kirim email OTP verifikasi akun ke {$userEmail}: " . $e->getMessage());
            }
        }

        if (!$sentWa && !$sentEmail) {
            session()->forget('account_verification_otp');
            session()->forget('account_verification_otp_sent_at');
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirimkan kode OTP via WhatsApp maupun Email. Silakan hubungi Administrator.',
            ], 500);
        }

        // Tambah count request OTP
        Cache::put($otpRequestCountKey, $requestCount + 1, 300);

        $channels = [];
        if ($sentWa) $channels[] = 'WhatsApp';
        if ($sentEmail) $channels[] = "Email ({$userEmail})";
        $channelText = implode(' & ', $channels);

        return response()->json([
            'success' => true,
            'message' => "Kode OTP berhasil dikirimkan via {$channelText}."
        ]);
    }

    /**
     * Memproses Verifikasi Kode OTP dari Pengguna
     */
    public function store(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $personel = $user->personel;

        if (!$personel) {
            return back()->withErrors(['otp' => 'Profil personel tidak ditemukan.']);
        }

        $otpData = session()->get('account_verification_otp');

        if (!$otpData || now()->greaterThan($otpData['expires_at']) || $otpData['code'] !== $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP tidak cocok atau telah kedaluwarsa. Silakan request kode baru.']);
        }

        // Hapus session OTP
        session()->forget('account_verification_otp');

        // Tandai akun sebagai terverifikasi (kolom face_verified diset true agar kompatibel dengan modul lain)
        $personel->update([
            'face_verified' => true
        ]);

        // Audit Trail Log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'VERIFY_ACCOUNT_OTP',
            'model_type' => 'App\Models\Personel',
            'model_id' => $personel->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $user->notify(new \App\Notifications\SystemNotification(
            'Verifikasi Akun Sukses',
            'Akun Sisfopers Anda telah berhasil diverifikasi via OTP WhatsApp.',
            'success',
            route('personel.dashboard')
        ));

        return redirect()->route('personel.sinyalmen.create');
    }
}