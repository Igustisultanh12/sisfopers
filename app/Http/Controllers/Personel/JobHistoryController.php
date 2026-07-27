<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\JobHistory;
use App\Models\JobOtpVerification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class JobHistoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $personel = $user->personel;

        // Tarik riwayat pekerjaan diurutkan berdasarkan pekerjaan aktif saat ini dahulu, lalu tanggal dibuat terbaru
        $histories = JobHistory::where('personel_id', $personel->id)
            ->orderBy('is_current', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Personel/JobHistory/Index', [
            'personel' => $personel,
            'histories' => $histories
        ]);
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'action_type' => 'required|string|in:INITIAL_FILL,UPDATE_JOB,PHK'
        ]);

        $user     = auth()->user();
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

        // Proteksi Cooldown: 60 detik sebelum bisa minta OTP baru
        $lastOtp = JobOtpVerification::where('personel_id', $personel->id)->latest()->first();
        if ($lastOtp && $lastOtp->created_at->addSeconds(60)->isFuture()) {
            $secondsLeft = now()->diffInSeconds($lastOtp->created_at->addSeconds(60));
            return response()->json([
                'success' => false,
                'message' => "Harap tunggu {$secondsLeft} detik sebelum meminta OTP kembali.",
            ], 429);
        }

        // Proteksi Rate Limiting: maks 3 permintaan dalam 5 menit, jika lebih blokir 5 menit
        $otpCount = JobOtpVerification::where('personel_id', $personel->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();
        if ($otpCount >= 3) {
            // Blokir selama 5 menit (300 detik)
            Cache::put($lockKey, true, 300);
            return response()->json([
                'success' => false,
                'locked'  => true,
                'seconds_left' => 300,
                'message' => 'Batas pengiriman OTP terlampaui. Akun Anda diblokir dari pengiriman OTP selama 5 menit.',
            ], 429);
        }

        $otpCode   = strval(rand(100000, 999999));
        $expiredAt = now()->addMinutes(10);

        $otpVerification = JobOtpVerification::create([
            'personel_id'  => $personel->id,
            'otp_code'     => $otpCode,
            'phone_number' => $personel->phone_number,
            'action_type'  => $request->action_type,
            'is_used'      => false,
            'expired_at'   => $expiredAt,
        ]);

        // Kirim OTP via WhatsApp Gateway & Email
        $actionText = match ($request->action_type) {
            'INITIAL_FILL' => 'pengisian data pekerjaan',
            'PHK'          => 'pelaporan PHK',
            default        => 'pembaruan data pekerjaan',
        };
        $message = "SISFOPERSKC: Kode OTP untuk {$actionText} Anda adalah *{$otpCode}*. Kode ini rahasia dan berlaku selama 10 menit.";

        $sentWa = false;
        if (!$disableWa) {
            $sentWa = \App\Services\WhatsappService::sendMessage($personel->phone_number, $message);
        }
        
        // Kirim via Email (Gmail / SMTP)
        $sentEmail = false;
        $userEmail = $user->email;
        if ($userEmail) {
            try {
                \Illuminate\Support\Facades\Mail::to($userEmail)->send(
                    new \App\Mail\OtpNotificationMail(
                        $personel->full_name,
                        $personel->nikc,
                        $otpCode,
                        'Verifikasi Pekerjaan (' . strtoupper($request->action_type) . ')',
                        '10 Menit'
                    )
                );
                $sentEmail = true;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal kirim email OTP ke {$userEmail}: " . $e->getMessage());
            }
        }

        if (!$sentWa && !$sentEmail) {
            $otpVerification->delete();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirimkan kode OTP via WhatsApp maupun Email. Silakan periksa jaringan / kontak Administrator.',
            ], 500);
        }

        $sentChannel = [];
        if ($sentWa) $sentChannel[] = 'WhatsApp';
        if ($sentEmail) $sentChannel[] = "Email ({$userEmail})";
        $channelText = implode(' & ', $sentChannel);

        // Audit Trail
        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'REQUEST_JOB_OTP',
            'model_type' => 'App\Models\Personel',
            'model_id'   => $personel->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success'        => true,
            'disable_wa_otp' => $disableWa,
            'channel_text'   => $channelText,
            'message'        => "Kode verifikasi OTP 6 digit telah dikirimkan ke {$channelText} Anda.",
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code'    => 'required|string|size:6',
            'action_type' => 'required|string|in:INITIAL_FILL,UPDATE_JOB,PHK'
        ]);

        $user = auth()->user();
        $personel = $user->personel;

        // Proteksi Blokir Percobaan
        $lockKey = "otp_lock_{$personel->id}";
        if (Cache::has($lockKey)) {
            $secondsLeft = Cache::ttl($lockKey);
            return response()->json([
                'success' => false,
                'locked' => true,
                'seconds_left' => $secondsLeft,
                'message' => "Batas percobaan terlampaui. Harap tunggu {$secondsLeft} detik lagi."
            ], 423);
        }

        $verification = JobOtpVerification::where('personel_id', $personel->id)
            ->where('otp_code', $request->otp_code)
            ->where('action_type', $request->action_type)
            ->where('is_used', false)
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        $attemptKey = "otp_attempts_{$personel->id}";

        if (!$verification) {
            // Naikkan jumlah percobaan gagal
            $attempts = Cache::get($attemptKey, 0) + 1;
            Cache::put($attemptKey, $attempts, now()->addMinutes(10));

            if ($attempts >= 5) {
                // Kunci selama 5 menit
                Cache::put($lockKey, true, now()->addMinutes(5));
                Cache::forget($attemptKey); // reset hitungan percobaan setelah dikunci
                return response()->json([
                    'success' => false,
                    'locked' => true,
                    'seconds_left' => 300,
                    'message' => 'Anda telah salah memasukkan OTP sebanyak 5 kali. Akses verifikasi Anda diblokir selama 5 menit.'
                ], 423);
            }

            $remaining = 5 - $attempts;
            return response()->json([
                'success' => false,
                'message' => "Kode OTP tidak cocok atau telah kedaluwarsa. Sisa percobaan: {$remaining} kali."
            ], 422);
        }

        // Hapus hitungan percobaan salah jika verifikasi berhasil
        Cache::forget($attemptKey);

        $verification->update(['is_used' => true]);

        // Simpan sesi terverifikasi ke dalam PHP Session (Valid selama 15 menit)
        session()->put('job_otp_verified_' . $personel->id, [
            'action_type' => $request->action_type,
            'verified_at' => now()->timestamp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil diverifikasi.'
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $personel = $user->personel;

        // Validasi Sesi OTP
        $sessionKey = 'job_otp_verified_' . $personel->id;
        if (!session()->has($sessionKey)) {
            return redirect()->back()->with('error', 'Aksi ditolak. Harap lakukan verifikasi OTP WhatsApp terlebih dahulu.');
        }

        $sessionData = session()->get($sessionKey);
        if (now()->timestamp - $sessionData['verified_at'] > 900) {
            session()->forget($sessionKey);
            return redirect()->back()->with('error', 'Sesi OTP Anda telah kadaluarsa. Harap ulangi verifikasi OTP.');
        }

        $request->validate([
            'tipe_pekerjaan' => 'required|in:ASN,SWASTA,WIRASWASTA,PELAJAR,TIDAK_BEKERJA,PHK',
        ]);

        $isPhk = ($request->tipe_pekerjaan === 'PHK');

        if ($request->tipe_pekerjaan === 'ASN') {
            $request->validate([
                'nama_perusahaan' => 'required|string|max:255', // Nama Instansi
                'jabatan' => 'required|string|max:255',
                'nip' => 'required|string|max:100',
                'asn_jenis' => 'required|in:CPNS,PNS,P3K,P3K Paruh Waktu',
                'asn_tmt' => 'required|date',
                'asn_sk' => 'nullable|file|max:2048',
                'provinsi' => ['required', 'string', 'max:255', Rule::exists('master_provinsi', 'nama')->where('is_active', true)],
                'kabupaten' => 'required|string|max:255',
                'kecamatan' => 'required|string|max:255',
                'alamat_lengkap' => 'required|string',
                'postal_code' => 'required|string|max:10',
            ]);
        } elseif ($isPhk) {
            $request->validate([
                'tmt_phk' => 'required|date',
                'alasan_phk' => 'required|string',
            ]);
        } elseif ($request->tipe_pekerjaan === 'TIDAK_BEKERJA') {
            // Tidak Bekerja murni (tmt_phk & alasan_phk opsional)
            $request->validate([
                'tmt_phk' => 'nullable|date',
                'alasan_phk' => 'nullable|string',
            ]);
        } else {
            $request->validate([
                'nama_perusahaan' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'nomor_karyawan' => 'nullable|string|max:100',
                'tmt_mulai' => 'required|date',
                'provinsi' => ['required', 'string', 'max:255', Rule::exists('master_provinsi', 'nama')->where('is_active', true)],
                'kabupaten' => 'required|string|max:255',
                'kecamatan' => 'required|string|max:255',
                'alamat_lengkap' => 'required|string',
                'postal_code' => 'required|string|max:10',
            ]);
        }

        // Hapus token verifikasi dari session agar tidak bisa di-reuse
        session()->forget($sessionKey);

        // Nonaktifkan pekerjaan aktif terakhir
        JobHistory::where('personel_id', $personel->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        if ($request->tipe_pekerjaan === 'ASN') {
            $personel->update([
                'is_asn' => true,
                'asn_nip' => $request->nip,
                'asn_jenis' => $request->asn_jenis,
                'asn_tmt' => $request->asn_tmt,
            ]);

            if ($request->hasFile('asn_sk')) {
                $path = $request->file('asn_sk')->store('personel/asn_sks', 'private');
                $personel->update(['asn_sk' => $path]);
            }

            $job = JobHistory::create([
                'personel_id' => $personel->id,
                'tipe_pekerjaan' => 'ASN',
                'nama_perusahaan' => $request->nama_perusahaan,
                'jabatan' => $request->jabatan,
                'nip' => $request->nip,
                'tmt_mulai' => $request->asn_tmt,
                'provinsi' => $request->provinsi,
                'kabupaten' => $request->kabupaten,
                'kecamatan' => $request->kecamatan,
                'alamat_lengkap' => $request->alamat_lengkap,
                'kode_pos' => $request->postal_code,
                'is_current' => true,
            ]);

        } elseif ($request->tipe_pekerjaan === 'TIDAK_BEKERJA' || $isPhk) {
            $prevJob = JobHistory::where('personel_id', $personel->id)->latest()->first();

            $job = JobHistory::create([
                'personel_id' => $personel->id,
                'tipe_pekerjaan' => 'TIDAK_BEKERJA',
                'nama_perusahaan' => $isPhk ? 'Terkena PHK / Menganggur' : 'Tidak Bekerja / Sedang Mencari Kerja',
                'tmt_mulai' => $isPhk ? $request->tmt_phk : now()->toDateString(),
                'provinsi' => $prevJob ? $prevJob->provinsi : $personel->province,
                'kabupaten' => $prevJob ? $prevJob->kabupaten : $personel->city,
                'kecamatan' => $prevJob ? $prevJob->kecamatan : $personel->district,
                'alamat_lengkap' => $prevJob ? $prevJob->alamat_lengkap : $personel->address,
                'kode_pos' => $prevJob ? $prevJob->kode_pos : $personel->postal_code,
                'is_phk' => $isPhk,
                'tmt_phk' => $isPhk ? $request->tmt_phk : null,
                'alasan_phk' => $isPhk ? $request->alasan_phk : null,
                'is_current' => true,
            ]);

            if ($isPhk && $prevJob && $prevJob->tipe_pekerjaan !== 'TIDAK_BEKERJA') {
                $prevJob->update([
                    'is_phk' => true,
                    'tmt_phk' => $request->tmt_phk,
                    'alasan_phk' => $request->alasan_phk,
                ]);
            }

            if ($personel->is_asn) {
                $personel->update(['is_asn' => false]);
            }

        } else {
            $job = JobHistory::create([
                'personel_id' => $personel->id,
                'tipe_pekerjaan' => $request->tipe_pekerjaan,
                'nama_perusahaan' => $request->nama_perusahaan,
                'jabatan' => $request->jabatan,
                'nomor_karyawan' => $request->nomor_karyawan,
                'tmt_mulai' => $request->tmt_mulai,
                'provinsi' => $request->provinsi,
                'kabupaten' => $request->kabupaten,
                'kecamatan' => $request->kecamatan,
                'alamat_lengkap' => $request->alamat_lengkap,
                'kode_pos' => $request->postal_code,
                'is_current' => true,
            ]);

            if ($personel->is_asn) {
                $personel->update(['is_asn' => false]);
            }
        }

        // Catat Audit Trail
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE_JOB_HISTORY',
            'model_type' => 'App\Models\JobHistory',
            'model_id' => $job->id,
            'new_values' => $job->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $user->notify(new \App\Notifications\SystemNotification(
            'Riwayat Pekerjaan Diperbarui',
            "Data pekerjaan aktif Anda berhasil diperbarui di perusahaan/satuan kerja: {$job->nama_perusahaan}.",
            'job',
            route('personel.job.index')
        ));

        return redirect()->route('personel.job.index')->with('success', 'Riwayat pekerjaan berhasil diperbarui.');
    }

    public function phk(Request $request)
    {
        $user = auth()->user();
        $personel = $user->personel;

        // Validasi Sesi OTP
        $sessionKey = 'job_otp_verified_' . $personel->id;
        if (!session()->has($sessionKey)) {
            return redirect()->back()->with('error', 'Aksi ditolak. Harap lakukan verifikasi OTP WhatsApp terlebih dahulu.');
        }

        $sessionData = session()->get($sessionKey);
        if (now()->timestamp - $sessionData['verified_at'] > 900) {
            session()->forget($sessionKey);
            return redirect()->back()->with('error', 'Sesi OTP Anda telah kadaluarsa. Harap ulangi verifikasi OTP.');
        }

        $request->validate([
            'tmt_phk' => 'required|date',
            'alasan_phk' => 'required|string',
        ]);

        // Hapus token verifikasi
        session()->forget($sessionKey);

        // Cari pekerjaan aktif saat ini
        $currentJob = JobHistory::where('personel_id', $personel->id)
            ->where('is_current', true)
            ->first();

        if ($currentJob) {
            $oldValues = $currentJob->toArray();

            // Setel menjadi nonaktif dan rekam data PHK-nya
            $currentJob->update([
                'is_current' => false,
                'is_phk' => true,
                'tmt_phk' => $request->tmt_phk,
                'alasan_phk' => $request->alasan_phk,
            ]);

            // Audit Trail
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'REPORT_PHK',
                'model_type' => 'App\Models\JobHistory',
                'model_id' => $currentJob->id,
                'old_values' => $oldValues,
                'new_values' => $currentJob->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        // Tambahkan baris baru dengan status TIDAK_BEKERJA
        JobHistory::create([
            'personel_id' => $personel->id,
            'tipe_pekerjaan' => 'TIDAK_BEKERJA',
            'nama_perusahaan' => 'Tidak Bekerja / Terkena PHK',
            'tmt_mulai' => $request->tmt_phk,
            'provinsi' => $currentJob ? $currentJob->provinsi : $personel->province,
            'kabupaten' => $currentJob ? $currentJob->kabupaten : $personel->city,
            'kecamatan' => $currentJob ? $currentJob->kecamatan : $personel->district,
            'alamat_lengkap' => $currentJob ? $currentJob->alamat_lengkap : $personel->address,
            'kode_pos' => $currentJob ? $currentJob->kode_pos : $personel->postal_code,
            'is_current' => true,
        ]);

        $user->notify(new \App\Notifications\SystemNotification(
            'Laporan PHK Berhasil',
            'Status pemutusan hubungan kerja (PHK) Anda berhasil dilaporkan ke sistem.',
            'job',
            route('personel.job.index')
        ));

        return redirect()->route('personel.job.index')->with('success', 'Status pemutusan hubungan kerja (PHK) berhasil dilaporkan.');
    }
}
