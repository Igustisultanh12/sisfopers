<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Menampilkan Tampilan Lembar Manajemen Pusat Akun (ROMEI Style)
     */
    public function edit(Request $request)
    {
        $user = Auth::user();
        
        // Mengambil profil data tambahan jika pengguna adalah personel komponen cadangan
        $personelData = null;
        if ($user->hasRole('personel')) {
            $user->load('personel');
            $personelData = $user->personel;
        }

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'phone_number' => $user->personel ? $user->personel->phone_number : ($user->phone_number ?? ''),
                'created_at' => $user->created_at->translatedFormat('d M Y (H:i)') . ' WIB',
                'google2fa_enabled' => (bool) $user->google2fa_secret,
            ],
            'role_name' => $user->role ? $user->role->name : 'user',
            'personel' => $personelData
        ]);
    }

    /**
     * Memproses Pembaruan Data Profil Utama, Foto Profil, & Kontak WhatsApp
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $dataUpdate = [
            'username' => $request->username,
            'email' => $request->email,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $dataUpdate['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($dataUpdate);

        if ($user->hasRole('personel') && $user->personel) {
            $user->personel->update([
                'phone_number' => $request->phone_number
            ]);
        } else {
            $user->update([
                'phone_number' => $request->phone_number
            ]);
        }

        $user->notify(new \App\Notifications\SystemNotification(
            'Profil Diperbarui',
            'Informasi profil personal akun Anda berhasil diperbarui.',
            'profile',
            route('profile.edit')
        ));

        return back()->with('success', 'Data informasi personal pusat akun Anda berhasil diperbarui.');
    }

    /**
     * Memproses Pembaruan Kata Sandi (Password) Pengguna
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        $request->user()->notify(new \App\Notifications\SystemNotification(
            'Kata Sandi Diperbarui',
            'Kata sandi keamanan gerbang masuk Anda berhasil diperbarui.',
            'password',
            route('profile.edit')
        ));

        return back()->with('success', 'Kata sandi keamanan gerbang masuk Anda berhasil diperbarui.');
    }

    /**
     * Mempersiapkan Kunci Rahasia & QR Code Tanpa Mengubah URL Bar di Browser
     */
    public function toggleMfa(Request $request)
    {
        $user = Auth::user();

        if ($user->google2fa_secret) {
            $user->google2fa_secret = null;
            $user->save();
            
            Auth::setUser($user); // Segarkan status autentikasi session
            
            $user->notify(new \App\Notifications\SystemNotification(
                'M2FA Dinonaktifkan',
                'Autentikasi Dua Faktor (Google MFA) berhasil dinonaktifkan.',
                'mfa',
                route('profile.edit')
            ));

            return redirect()->route('profile.edit')->with('success', 'Keamanan verifikasi M2FA Google Authenticator berhasil dinonaktifkan.');
        }

        $google2fa = app('pragmarx.google2fa');
        $secretKey = $google2fa->generateSecretKey();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            'SISFOPERSKC',
            $user->email,
            $secretKey
        );

        $inlineQrImage = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrCodeUrl);

        // SOLUSI: Menggunakan back()->with() agar URL bar browser tetap bersih terkunci di /account/settings
        return back()->with('mfa_setup', [
            'secret' => $secretKey,
            'qr_image' => $inlineQrImage
        ]);
    }

    /**
     * Memvalidasi 6-Digit OTP dari Pengguna untuk Mengaktifkan M2FA Secara Permanen
     */
    public function verifyMfa(Request $request)
    {
        $request->validate([
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey($request->secret, $request->code);

        if ($valid) {
            $user->google2fa_secret = $request->secret;
            $user->save();
            
            Auth::setUser($user); // Segarkan instance auth di session internal Laravel

            $user->notify(new \App\Notifications\SystemNotification(
                'M2FA Berhasil Diaktifkan',
                'Autentikasi Dua Faktor (Google MFA) berhasil diaktifkan untuk akun Anda.',
                'mfa',
                route('profile.edit')
            ));

            return redirect()->route('profile.edit')->with('success', 'M2FA Berhasil diaktifkan! Akun Anda kini terlindungi penuh.');
        }

        return back()->withErrors(['mfa_code' => 'Kode OTP yang Anda masukkan tidak valid atau sinkronisasi waktu perangkat salah.']);
    }
}