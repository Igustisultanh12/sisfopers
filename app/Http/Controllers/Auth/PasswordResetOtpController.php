<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class PasswordResetOtpController extends Controller
{
    /**
     * Tampilkan formulir reset password via OTP Admin
     */
    public function create()
    {
        return Inertia::render('Auth/ResetPasswordOtp');
    }

    /**
     * Memproses reset password menggunakan NIKC dan OTP
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.required' => 'NIKC wajib diisi.',
            'otp.required'      => 'Kode OTP wajib diisi.',
            'otp.size'          => 'Kode OTP harus tepat 6 digit.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min'      => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        // Batasi tebakan OTP per NIKC dan per IP untuk mencegah brute-force (OTP hanya 6 digit).
        $throttleKeys = [
            'pwreset-otp:nikc:' . trim($request->username),
            'pwreset-otp:ip:' . $request->ip(),
        ];
        foreach ($throttleKeys as $tk) {
            if (RateLimiter::tooManyAttempts($tk, 5)) {
                $seconds = RateLimiter::availableIn($tk);
                return back()->withErrors(['otp' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik atau minta OTP baru dari Admin."]);
            }
        }

        $personel = Personel::where('nikc', trim($request->username))
            ->with('user')
            ->first();

        if (!$personel) {
            foreach ($throttleKeys as $tk) { RateLimiter::hit($tk, 900); }
            return back()->withErrors(['username' => 'Personel dengan NIKC tersebut tidak terdaftar di sistem kami.']);
        }

        if (!$personel->reset_password_otp || !hash_equals((string) $personel->reset_password_otp, trim($request->otp))) {
            foreach ($throttleKeys as $tk) { RateLimiter::hit($tk, 900); }
            // Setelah 5 percobaan gagal, hanguskan OTP agar tidak bisa ditebak lebih lanjut.
            if (RateLimiter::attempts('pwreset-otp:nikc:' . trim($request->username)) >= 5) {
                $personel->update(['reset_password_otp' => null, 'reset_password_otp_expired_at' => null]);
            }
            return back()->withErrors(['otp' => 'Kode OTP Reset Password tidak cocok atau tidak valid.']);
        }

        if (now()->greaterThan($personel->reset_password_otp_expired_at)) {
            return back()->withErrors(['otp' => 'Kode OTP Reset Password telah kedaluwarsa. Silakan minta kode baru dari Admin.']);
        }

        $user = $personel->user;
        if (!$user) {
            return back()->withErrors(['username' => 'Akun sistem untuk personel tidak ditemukan.']);
        }

        // Reset password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Bersihkan OTP agar tidak bisa dipakai ulang
        $personel->update([
            'reset_password_otp'             => null,
            'reset_password_otp_expired_at'  => null,
        ]);

        // Reset counter throttle setelah berhasil.
        foreach ($throttleKeys as $tk) { RateLimiter::clear($tk); }

        // Audit Trail Log
        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'RESET_PASSWORD_VIA_OTP_ADMIN',
            'model_type' => 'App\Models\User',
            'model_id'   => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan login dengan kata sandi baru Anda.');
    }
}
