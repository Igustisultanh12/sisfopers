<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Personel;
use App\Models\Registration;
use App\Models\LoginLog;
use App\Models\SkepData;
use App\Models\MasterKepangkatan;
use App\Rules\NikcFormatRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['username' => 'Akun Anda belum aktif. Sila tunggu verifikasi Admin.']);
            }

            $userAgent = $request->userAgent();

            LoginLog::create([
                'user_id' => $user->id,
                'login_at' => now(),
                'ip_address' => $request->ip(),
                'browser' => $this->parseBrowser($userAgent),
                'os' => $this->parseOS($userAgent),
                'device' => \Illuminate\Support\Str::limit($userAgent, 45, ''),
            ]);

            $request->session()->regenerate();

            if ($user->hasRole('admin')) {
                return redirect()->intended(route('admin.dashboard'));
            }

            if ($user->hasRole('komandan')) {
                return redirect()->intended(route('komandan.dashboard'));
            }
            
            if ($user->hasRole('personel')) {
                if (!$user->personel || !$user->personel->face_verified) {
                    return redirect()->route('personel.face-verification');
                }
                return redirect()->intended(route('personel.dashboard'));
            }

            if ($user->hasRole('kordinator_angkatan') || $user->hasRole('kordinator_matra')) {
                return redirect()->intended(route('kordinator.dashboard'));
            }
        }

        return back()->withErrors(['username' => 'Kredensial login tidak cocok dengan data kami.']);
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register', [
            'pangkatOptions' => MasterKepangkatan::where('is_active', true)
                ->orderBy('urutan')
                ->get(['nama', 'kelompok_nikc', 'klaim_langsung']),
        ]);
    }

    public function register(Request $request)
    {
        if ($request->has('nikc')) {
            $request->merge(['nikc' => SkepData::reconstructNikc($request->nikc)]);
        }

        if ($request->filled('pangkat')) {
            $request->merge([
                'pangkat' => MasterKepangkatan::canonicalName($request->input('pangkat')),
            ]);
        }

        // Validasi data input form manual (karena modifikasi request structural)
        $validationRules = [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|lowercase|max:255|unique:users',
            'phone_number' => 'required|string|max:20',
            'nik' => 'required|string|size:16|unique:personels,nik',
            'matra' => 'required|in:AD,AL,AU',
            'angkatan' => 'required|string|max:4',
            'pangkat' => ['required', Rule::exists('master_kepangkatan', 'nama')->where('is_active', true)],
            'nikc' => ['required', 'digits:17', 'numeric', new NikcFormatRule($request->input('province'), $request->input('dob'), $request->input('pangkat')), 'unique:personels,nikc'],
            'sumber_rekrutmen' => 'required|in:Reguler,SPPI,PNS', // Bidang rekrutmen baru
            'pob' => 'required|string|max:255',
            'dob' => 'required|date',
            'gender' => 'required|in:L,P',
            'address' => 'required|string',
            'province' => ['required', 'string', 'max:255', Rule::exists('master_provinsi', 'nama')->where('is_active', true)],
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'photo_profile' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'ktp_document' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'required|string|min:8|confirmed',
            'is_asn' => 'boolean',
        ];

        if ($request->boolean('is_asn')) {
            $validationRules['asn_nip'] = 'required|string|max:100';
            $validationRules['asn_jenis'] = 'required|in:CPNS,PNS,P3K,P3K Paruh Waktu';
            $validationRules['asn_tmt'] = 'required|date';
            $validationRules['asn_sk'] = 'nullable|file|mimes:pdf,png,jpg,jpeg|max:2048';
            $validationRules['asn_nama_instansi'] = 'required|string|max:255';
        }

        $request->validate($validationRules);

        // Verifikasi NIKC pra-pendaftaran di database SKEP
        $skep = SkepData::where('nikc', trim($request->nikc))->first();
        if (!$skep) {
            return back()->withErrors(['nikc' => 'NIKC belum ada di database. Sistem tidak membuat NIKC; jika NIKC sudah tercantum di SKEP, silakan ajukan verifikasi berkas SKEP sebagai dasar admin menambahkan atau memverifikasi NIKC tersebut.']);
        }
        if (!$skep->dob || Carbon::parse($skep->dob)->toDateString() !== Carbon::parse($request->dob)->toDateString()) {
            return back()->withErrors(['dob' => 'Tanggal lahir tidak cocok dengan database SKEP untuk NIKC ini.']);
        }

        // Validasi silang nama dan matra dengan database SKEP agar tidak dimanipulasi
        if (strcasecmp(trim($skep->nama_lengkap), trim($request->full_name)) !== 0) {
            return back()->withErrors(['full_name' => 'Nama Lengkap tidak cocok dengan database SKEP untuk NIKC ini.']);
        }
        if (strtoupper($skep->matra) !== strtoupper($request->matra)) {
            return back()->withErrors(['matra' => 'Matra tidak cocok dengan database SKEP untuk NIKC ini.']);
        }
        if ($skep->pangkat && strcasecmp(MasterKepangkatan::canonicalName($skep->pangkat), trim($request->pangkat)) !== 0) {
            return back()->withErrors(['pangkat' => 'Pangkat tidak cocok dengan database SKEP untuk NIKC ini.']);
        }

        DB::beginTransaction();
        try {
            $rolePersonel = Role::where('name', 'personel')->first();

            // 1. Create System Account User
            $user = User::create([
                'uuid' => Str::uuid(),
                'role_id' => $rolePersonel->id,
                'username' => trim($request->nikc),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => false,
            ]);

            // 2. Handle File Uploads berkas pendaftaran (Disimpan di disk private aman)
            try {
                $photoPath = $request->file('photo_profile')->store('personel/photos', 'private');
            } catch (\Exception $e) {
                $photoPath = $request->file('photo_profile')->store('personel/photos', 'local');
            }

            try {
                $ktpPath = $request->file('ktp_document')->store('personel/documents', 'private');
            } catch (\Exception $e) {
                $ktpPath = $request->file('ktp_document')->store('personel/documents', 'local');
            }
            
            $asnSkPath = null;
            if ($request->hasFile('asn_sk')) {
                $asnSkPath = $request->file('asn_sk')->store('personel/asn_sks', 'private');
            }

            // 3. Create Detail Personel
            $personel = Personel::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'nik' => $request->nik,
                'full_name' => $request->full_name,
                'pob' => $request->pob,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'matra' => $request->matra,
                'angkatan' => $request->angkatan,
                'pangkat' => $request->pangkat, 
                'nikc' => $request->nikc,       
                'sumber_rekrutmen' => $request->sumber_rekrutmen, // Bidang rekrutmen baru disimpan
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'province' => $request->province,
                'city' => $request->city,
                'district' => $request->district,
                'village' => $request->village,
                'postal_code' => $request->postal_code,
                'photo_profile' => $photoPath,
                'ktp_document' => $ktpPath,
                'is_asn' => $request->boolean('is_asn'),
                'asn_nip' => $request->asn_nip,
                'asn_jenis' => $request->asn_jenis,
                'asn_tmt' => $request->asn_tmt,
                'asn_sk' => $asnSkPath,
            ]);

            // 3b. Sync Job History if registered as ASN
            if ($personel->is_asn) {
                \App\Models\JobHistory::create([
                    'personel_id' => $personel->id,
                    'tipe_pekerjaan' => 'ASN',
                    'nama_perusahaan' => $request->asn_nama_instansi,
                    'nip' => $request->asn_nip,
                    'tmt_mulai' => $request->asn_tmt,
                    'provinsi' => $personel->province,
                    'kabupaten' => $personel->city,
                    'kecamatan' => $personel->district,
                    'alamat_lengkap' => $personel->address,
                    'kode_pos' => $personel->postal_code,
                    'is_current' => true,
                ]);
            }

            // 4. Create Registration Antrean Verifikasi
            Registration::create([
                'personel_id' => $personel->id,
                'status_verification' => 'PENDING',
            ]);

            // Pemicu Notifikasi WA & Email ke Personel yang Mendaftar
            $msgPersonel = "Halo *{$personel->full_name}*, pendaftaran akun Anda di Sisfoperskc telah berhasil diajukan dan saat ini *MENUNGGU VERIFIKASI* berkas oleh administrator. Harap tunggu info selanjutnya.";
            \App\Services\WhatsappService::sendMessage($personel->phone_number, $msgPersonel);

            if ($user->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(
                        new \App\Mail\OtpNotificationMail(
                            $personel->full_name,
                            $personel->nikc,
                            'REG-SUCCESS',
                            'Pendaftaran Akun (Menunggu Verifikasi)',
                            'Status Pending'
                        )
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Gagal kirim email pendaftaran ke {$user->email}: " . $e->getMessage());
                }
            }

            // Pemicu Notifikasi Database, Email, & WA ke Seluruh Admin & Koordinator
            $adminsAndCoordinators = \App\Models\User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'kordinator_angkatan', 'kordinator_matra']))
                ->with('personel')
                ->get();
                
            foreach ($adminsAndCoordinators as $recipientUser) {
                // Kirim notifikasi aplikasi (database & email mailable)
                $recipientUser->notify(new \App\Notifications\NewRegistrationNotification($personel));

                if ($recipientUser->personel && $recipientUser->personel->phone_number) {
                    $msgAdmin = "🔔 *PENDAFTARAN BARU PERLU DIVERIFIKASI*\n\n"
                        . "Nama: {$personel->full_name}\n"
                        . "NIKC: " . ($personel->nikc ?? '-') . "\n"
                        . "Matra/Angkatan: {$personel->matra} / Angkatan {$personel->angkatan}\n\n"
                        . "Silakan login ke portal Sisfoperskc untuk meninjau berkas pendaftar.";
                    \App\Services\WhatsappService::sendMessage($recipientUser->personel->phone_number, $msgAdmin);
                }
            }

            DB::commit();

            return redirect()->route('login')->with('success', 'Pendaftaran berhasil. Silakan tunggu verifikasi admin untuk mengaktifkan akun Anda.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal melakukan registrasi: ' . $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function parseBrowser($userAgent): string
    {
        if (empty($userAgent)) return 'Unknown';
        if (str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident')) return 'Internet Explorer';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR')) return 'Opera';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        return 'Unknown Browser';
    }

    private function parseOS($userAgent): string
    {
        if (empty($userAgent)) return 'Unknown';
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS X')) return 'Mac OS';
        if (str_contains($userAgent, 'X11') || str_contains($userAgent, 'Linux')) return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) return 'iOS';
        return 'Unknown OS';
    }
}
