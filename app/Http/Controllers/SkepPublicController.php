<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SkepData;
use App\Models\SkepRequest;
use App\Models\MasterKepangkatan;
use App\Rules\NikcFormatRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SkepPublicController extends Controller
{
    public function checkNikc(Request $request)
    {
        if ($request->has('nikc')) {
            $request->merge(['nikc' => SkepData::reconstructNikc($request->nikc)]);
        }

        $request->validate([
            'dob' => 'required|date',
            'nikc' => ['required', 'digits:17', 'numeric', new NikcFormatRule(null, $request->input('dob'), $request->input('pangkat'))],
        ]);

        $rateLimitKey = 'skep-check:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 120)) {
            return response()->json([
                'message' => 'Terlalu banyak percobaan. Silakan coba kembali setelah 1 menit.',
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 60);

        $nikc = trim($request->nikc);
        $dob = Carbon::parse($request->dob)->toDateString();

        // Check if NIKC is already registered in personels
        $alreadyRegistered = \App\Models\Personel::where('nikc', $nikc)
            ->whereDate('dob', $dob)
            ->exists();

        if ($alreadyRegistered) {
            RateLimiter::clear($rateLimitKey);
            return response()->json([
                'exists' => true,
                'registered' => true,
            ]);
        }

        $skep = SkepData::where('nikc', $nikc)
            ->whereDate('dob', $dob)
            ->first();

        if ($skep) {
            RateLimiter::clear($rateLimitKey);
            return response()->json([
                'exists' => true,
                'registered' => false,
                'data' => [
                    'nama_lengkap' => $skep->nama_lengkap,
                    'pangkat' => MasterKepangkatan::canonicalName($skep->pangkat),
                    'matra' => $skep->matra,
                    'angkatan' => $skep->angkatan,
                ]
            ]);
        }

        RateLimiter::clear($rateLimitKey);
        return response()->json([
            'exists' => false,
        ]);
    }

    /**
     * Memproses unggah berkas pengajuan SKEP baru (PDF)
     */
    public function submitRequest(Request $request)
    {
        if ($request->has('nikc')) {
            $request->merge(['nikc' => SkepData::reconstructNikc($request->nikc)]);
        }

        if ($request->filled('pangkat')) {
            $request->merge([
                'pangkat' => MasterKepangkatan::canonicalName($request->input('pangkat')),
            ]);
        }

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'dob' => 'required|date',
            'pangkat' => ['required', Rule::exists('master_kepangkatan', 'nama')->where('is_active', true)],
            'nik' => 'required|string|size:16',
            'nikc' => ['required', 'digits:17', 'numeric', new NikcFormatRule(null, $request->input('dob'), $request->input('pangkat'))],
            'angkatan' => 'required|string|max:4',
            'matra' => 'required|in:AD,AL,AU',
            'phone_number' => 'required|string|max:20',
            'skep_file' => 'required|file|mimes:pdf|max:2048', // Khusus PDF maksimal 2MB
        ]);

        try {
            // Upload berkas PDF SKEP
            $filePath = $request->file('skep_file')->store('personel/skep_requests', 'public');

            $skepRequest = SkepRequest::create([
                'uuid' => Str::uuid(),
                'nama_lengkap' => $request->nama_lengkap,
                'dob' => $request->dob,
                'pangkat' => $request->pangkat,
                'nik' => $request->nik,
                'nikc' => $request->nikc,
                'angkatan' => $request->angkatan,
                'matra' => $request->matra,
                'phone_number' => $request->phone_number,
                'skep_file' => $filePath,
                'status' => 'PENDING',
            ]);

            // Kirim notifikasi WA ke pemohon
            $msgRequest = "Halo *{$request->nama_lengkap}*, pengajuan verifikasi berkas SKEP Anda untuk NIKC *{$request->nikc}* telah diterima dan *SEDANG DITINJAU* oleh administrator. Hasil verifikasi akan diinfokan kembali.";
            \App\Services\WhatsappService::sendMessage($request->phone_number, $msgRequest);

            // Kirim notifikasi WA ke seluruh Admin
            $admins = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'admin'))
                ->with('personel')
                ->get();
                
            foreach ($admins as $recipientUser) {
                if ($recipientUser->personel && $recipientUser->personel->phone_number) {
                    $msgAdmin = "🔔 *PENGAJUAN VERIFIKASI SKEP BARU*\n\n"
                        . "Nama: {$request->nama_lengkap}\n"
                        . "NIK: {$request->nik}\n"
                        . "NIKC: {$request->nikc}\n"
                        . "Matra/Angkatan: {$request->matra} / Angkatan {$request->angkatan}\n\n"
                        . "Silakan login ke dashboard Admin SISFOPERS untuk memproses pengajuan berkas SKEP ini.";
                    \App\Services\WhatsappService::sendMessage($recipientUser->personel->phone_number, $msgAdmin);
                }
            }

            RateLimiter::clear($rateLimitKey);

            return back()->with('success', 'Pengajuan verifikasi berkas SKEP berhasil dikirim. Harap tunggu pengecekan Admin dan notifikasi via WhatsApp.');
        } catch (\Exception $e) {
            RateLimiter::clear($rateLimitKey);
            return back()->withErrors(['error' => 'Gagal mengunggah berkas pengajuan: ' . $e->getMessage()]);
        }
    }
}
