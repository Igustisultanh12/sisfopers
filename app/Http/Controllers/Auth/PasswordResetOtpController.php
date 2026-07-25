<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $personel = Personel::where('nikc', trim($request->username))
            ->with('user')
            ->first();

        if (!$personel) {
            return back()->withErrors(['username' => 'Personel dengan NIKC tersebut tidak terdaftar di sistem kami.']);
        }

        if (!$personel->reset_password_otp || $personel->reset_password_otp !== trim($request->otp)) {
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
