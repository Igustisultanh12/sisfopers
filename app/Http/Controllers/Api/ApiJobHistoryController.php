<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobHistory;
use App\Models\JobOtpVerification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ApiJobHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $personel = $user->personel;

        if (!$personel) {
            return response()->json([
                'success' => false,
                'message' => 'Data personel Anda tidak ditemukan.'
            ], 404);
        }

        $histories = JobHistory::where('personel_id', $personel->id)
            ->orderBy('is_current', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'histories' => $histories
        ]);
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'action_type' => 'required|string|in:INITIAL_FILL,UPDATE_JOB,PHK'
        ]);

        $user = $request->user();
        $personel = $user->personel;

        if (!$personel) {
            return response()->json([
                'success' => false,
                'message' => 'Data personel Anda tidak ditemukan.'
            ], 404);
        }

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

        $lastOtp = JobOtpVerification::where('personel_id', $personel->id)
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at->addSeconds(60)->isFuture()) {
            $secondsLeft = now()->diffInSeconds($lastOtp->created_at->addSeconds(60));
            return response()->json([
                'success' => false,
                'message' => "Harap tunggu {$secondsLeft} detik lagi sebelum meminta OTP kembali."
            ], 429);
        }

        $otpCount = JobOtpVerification::where('personel_id', $personel->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($otpCount >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Batas pengiriman OTP terlampaui. Silakan coba kembali dalam 5 menit.'
            ], 429);
        }

        $otpCode = strval(rand(100000, 999999));
        $expiredAt = now()->addMinutes(10);

        $otpVerification = JobOtpVerification::create([
            'personel_id' => $personel->id,
            'otp_code' => $otpCode,
            'phone_number' => $personel->phone_number,
            'action_type' => $request->action_type,
            'is_used' => false,
            'expired_at' => $expiredAt,
        ]);

        $actionText = $request->action_type === 'INITIAL_FILL' ? 'pengisian data' : ($request->action_type === 'PHK' ? 'pelaporan PHK' : 'pembaruan data');
        $message = "SISFOPERSKC: Kode OTP untuk {$actionText} pekerjaan Anda adalah *{$otpCode}*. Kode ini rahasia dan berlaku selama 10 menit.";
        
        $sent = \App\Services\WhatsappService::sendMessage($personel->phone_number, $message);
        if (!$sent) {
            $otpVerification->delete();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirimkan kode OTP via WhatsApp.'
            ], 500);
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'REQUEST_JOB_OTP',
            'model_type' => 'App\Models\Personel',
            'model_id' => $personel->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil dikirimkan ke nomor WhatsApp Anda.'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
            'action_type' => 'required|string|in:INITIAL_FILL,UPDATE_JOB,PHK'
        ]);

        $user = $request->user();
        $personel = $user->personel;

        if (!$personel) {
            return response()->json([
                'success' => false,
                'message' => 'Data personel Anda tidak ditemukan.'
            ], 404);
        }

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
            $attempts = Cache::get($attemptKey, 0) + 1;
            Cache::put($attemptKey, $attempts, now()->addMinutes(10));

            if ($attempts >= 5) {
                Cache::put($lockKey, true, now()->addMinutes(5));
                Cache::forget($attemptKey);
                return response()->json([
                    'success' => false,
                    'locked' => true,
                    'seconds_left' => 300,
                    'message' => 'Anda telah salah memasukkan OTP sebanyak 5 kali. Akses verifikasi diblokir selama 5 menit.'
                ], 423);
            }

            $remaining = 5 - $attempts;
            return response()->json([
                'success' => false,
                'message' => "Kode OTP tidak cocok atau telah kedaluwarsa. Sisa percobaan: {$remaining} kali."
            ], 422);
        }

        Cache::forget($attemptKey);
        $verification->update(['is_used' => true]);

        // Simpan token otentikasi OTP ke Cache (Valid selama 15 menit)
        Cache::put("job_otp_verified_token_{$personel->id}", $request->action_type, now()->addMinutes(15));

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil diverifikasi.'
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $personel = $user->personel;

        if (!$personel) {
            return response()->json([
                'success' => false,
                'message' => 'Data personel Anda tidak ditemukan.'
            ], 404);
        }

        $tokenKey = "job_otp_verified_token_{$personel->id}";
        if (!Cache::has($tokenKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Aksi ditolak. Harap lakukan verifikasi OTP terlebih dahulu.'
            ], 403);
        }

        $request->validate([
            'tipe_pekerjaan' => 'required|string|in:ASN,SWASTA,WIRASWASTA,PELAJAR,TIDAK_BEKERJA',
            'nama_perusahaan' => 'required_unless:tipe_pekerjaan,TIDAK_BEKERJA|string|max:150',
            'jabatan' => 'nullable|string|max:100',
            'nomor_karyawan' => 'nullable|string|max:50',
            'nip' => 'required_if:tipe_pekerjaan,ASN|nullable|string|max:50',
            'tmt_mulai' => 'required|date',
            'provinsi' => ['required', 'string', 'max:100', Rule::exists('master_provinsi', 'nama')->where('is_active', true)],
            'kabupaten' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'alamat_lengkap' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'tmt_phk' => 'required_if:tipe_pekerjaan,TIDAK_BEKERJA|nullable|date',
            'alasan_phk' => 'required_if:tipe_pekerjaan,TIDAK_BEKERJA|nullable|string|max:255',
        ]);

        // Nonaktifkan pekerjaan aktif yang lama
        JobHistory::where('personel_id', $personel->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        if ($request->tipe_pekerjaan === 'ASN') {
            $job = JobHistory::create([
                'personel_id' => $personel->id,
                'tipe_pekerjaan' => 'ASN',
                'nama_perusahaan' => $request->nama_perusahaan,
                'jabatan' => $request->jabatan,
                'nip' => $request->nip,
                'tmt_mulai' => $request->tmt_mulai,
                'provinsi' => $request->provinsi,
                'kabupaten' => $request->kabupaten,
                'kecamatan' => $request->kecamatan,
                'alamat_lengkap' => $request->alamat_lengkap,
                'kode_pos' => $request->postal_code,
                'is_current' => true,
            ]);

            $personel->update(['is_asn' => true]);

        } elseif ($request->tipe_pekerjaan === 'TIDAK_BEKERJA') {
            $prevJob = JobHistory::where('personel_id', $personel->id)->latest()->first();

            $job = JobHistory::create([
                'personel_id' => $personel->id,
                'tipe_pekerjaan' => 'TIDAK_BEKERJA',
                'nama_perusahaan' => 'Tidak Bekerja / Terkena PHK',
                'tmt_mulai' => $request->tmt_phk,
                'provinsi' => $prevJob ? $prevJob->provinsi : $personel->province,
                'kabupaten' => $prevJob ? $prevJob->kabupaten : $personel->city,
                'kecamatan' => $prevJob ? $prevJob->kecamatan : $personel->district,
                'alamat_lengkap' => $prevJob ? $prevJob->alamat_lengkap : $personel->address,
                'kode_pos' => $prevJob ? $prevJob->kode_pos : $personel->postal_code,
                'is_phk' => true,
                'tmt_phk' => $request->tmt_phk,
                'alasan_phk' => $request->alasan_phk,
                'is_current' => true,
            ]);

            if ($prevJob && $prevJob->tipe_pekerjaan !== 'TIDAK_BEKERJA') {
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

        // Hapus token OTP dari Cache
        Cache::forget($tokenKey);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE_JOB_HISTORY',
            'model_type' => 'App\Models\JobHistory',
            'model_id' => $job->id,
            'new_values' => $job->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Kirim Notifikasi Lonceng
        $user->notify(new \App\Notifications\SystemNotification(
            'Riwayat Pekerjaan Diperbarui',
            "Data pekerjaan aktif Anda berhasil diperbarui di: {$job->nama_perusahaan}.",
            'job',
            route('personel.job.index')
        ));

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pekerjaan berhasil diperbarui.'
        ]);
    }
}
