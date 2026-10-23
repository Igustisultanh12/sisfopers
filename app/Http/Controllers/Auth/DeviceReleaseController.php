<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SystemNotificationMail;
use App\Models\Pju;
use App\Models\User;
use App\Support\DeviceGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Pelepasan perangkat aplikasi lewat tautan email (bila pengguna kehilangan akses HP lama).
 * Langkah: tautan email → NIKC + tanggal lahir + kata sandi → OTP email → semua perangkat dilepas.
 */
class DeviceReleaseController extends Controller
{
    private function load(?string $token): ?array
    {
        if (!$token || strlen($token) < 20) {
            return null;
        }
        $hash = hash('sha256', $token);
        $data = Cache::get('device_release:' . $hash);
        if (!is_array($data)) {
            return null;
        }
        $actor = $data['type'] === 'pju' ? Pju::find($data['id']) : User::with('personel')->find($data['id']);
        return $actor ? ['hash' => $hash, 'actor' => $actor, 'data' => $data] : null;
    }

    private function view(string $step, array $extra = [])
    {
        return response()->view('auth.device-release', array_merge(['step' => $step], $extra));
    }

    public function show(Request $request)
    {
        $ctx = $this->load($request->query('token'));
        if (!$ctx) {
            return $this->view('invalid');
        }
        return $this->view('verify', ['token' => $request->query('token'), 'isPju' => $ctx['actor'] instanceof Pju]);
    }

    public function verify(Request $request)
    {
        $token = (string) $request->input('token');
        $ctx = $this->load($token);
        if (!$ctx) {
            return $this->view('invalid');
        }
        $actor = $ctx['actor'];
        $isPju = $actor instanceof Pju;
        $request->validate([
            'identity' => 'required|string|max:150',
            'dob' => $isPju ? 'nullable|date' : 'required|date',
            'password' => 'required|string|max:255',
        ], [], ['identity' => $isPju ? 'username' : 'NIKC', 'dob' => 'tanggal lahir', 'password' => 'kata sandi']);

        $attemptKey = 'device_release_try:' . $ctx['hash'];
        $tries = (int) Cache::get($attemptKey, 0);
        if ($tries >= 5) {
            Cache::forget('device_release:' . $ctx['hash']);
            return $this->view('invalid', ['reason' => 'Terlalu banyak percobaan. Minta tautan baru dari aplikasi.']);
        }

        $identity = trim($request->input('identity'));
        $ok = Hash::check($request->input('password'), $actor->password);
        if ($isPju) {
            $ok = $ok && (strcasecmp($identity, (string) $actor->username) === 0 || strcasecmp($identity, (string) $actor->email) === 0);
        } else {
            $personel = $actor->personel;
            $nikcOk = $identity === (string) ($personel->nikc ?? '') || $identity === (string) $actor->username;
            $dobOk = $personel && $personel->dob && Carbon::parse($personel->dob)->toDateString() === Carbon::parse($request->input('dob'))->toDateString();
            $ok = $ok && $nikcOk && $dobOk;
        }

        if (!$ok) {
            Cache::put($attemptKey, $tries + 1, now()->addMinutes(30));
            return back()->withInput($request->except('password'))
                ->withErrors(['identity' => $isPju ? 'Data tidak cocok dengan akun. Periksa kembali username dan kata sandi.' : 'Data tidak cocok dengan akun. Periksa kembali NIKC, tanggal lahir, dan kata sandi.']);
        }

        $otp = (string) random_int(100000, 999999);
        Cache::put('device_release_otp:' . $ctx['hash'], ['otp' => Hash::make($otp), 'tries' => 0], now()->addMinutes(10));
        try {
            Mail::to($actor->email)->send(new SystemNotificationMail(
                'Kode OTP Pelepasan Perangkat',
                "Kode OTP untuk melepaskan perangkat lama dari akun SISFOPERS KC Anda adalah: {$otp}. Kode berlaku 10 menit. Jangan berikan kode ini kepada siapa pun.",
                $actor,
                '#'
            ));
        } catch (\Throwable $e) {
            Log::error('OTP pelepasan perangkat gagal: ' . $e->getMessage());
            return back()->withErrors(['identity' => 'Email OTP gagal dikirim. Silakan coba lagi.']);
        }

        return $this->view('otp', ['token' => $token, 'email' => DeviceGuard::maskEmail($actor->email)]);
    }

    public function confirm(Request $request)
    {
        $token = (string) $request->input('token');
        $ctx = $this->load($token);
        if (!$ctx) {
            return $this->view('invalid');
        }
        $request->validate(['otp' => 'required|digits:6'], [], ['otp' => 'kode OTP']);

        $key = 'device_release_otp:' . $ctx['hash'];
        $state = Cache::get($key);
        if (!is_array($state)) {
            return $this->view('verify', ['token' => $token, 'isPju' => $ctx['actor'] instanceof Pju, 'formError' => 'Kode OTP kedaluwarsa. Ulangi verifikasi data.']);
        }
        if (!Hash::check($request->input('otp'), $state['otp'])) {
            $state['tries'] = ($state['tries'] ?? 0) + 1;
            if ($state['tries'] >= 5) {
                Cache::forget($key);
                return $this->view('invalid', ['reason' => 'Kode OTP salah terlalu banyak. Minta tautan baru dari aplikasi.']);
            }
            Cache::put($key, $state, now()->addMinutes(10));
            return $this->view('otp', ['token' => $token, 'email' => DeviceGuard::maskEmail($ctx['actor']->email), 'otpError' => 'Kode OTP salah. Sisa percobaan: ' . (5 - $state['tries']) . '.']);
        }

        $count = DeviceGuard::releaseAllDevices($ctx['actor']);
        Cache::forget($key);
        Cache::forget('device_release:' . $ctx['hash']);
        Cache::forget('device_release_try:' . $ctx['hash']);

        try {
            \App\Models\AuditLog::create([
                'user_id' => $ctx['actor'] instanceof User ? $ctx['actor']->id : null,
                'action' => 'RELEASE_MOBILE_DEVICE',
                'model_type' => get_class($ctx['actor']),
                'model_id' => $ctx['actor']->getKey(),
                'new_values' => ['released_tokens' => $count],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // audit opsional
        }

        return $this->view('done');
    }
}
