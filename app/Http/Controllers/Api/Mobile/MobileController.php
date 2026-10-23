<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\MobileDevice;
use App\Models\Personel;
use App\Models\Pju;
use App\Models\Setting;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Endpoint khusus aplikasi Android SISFOPERSKC:
 * login token, profil sesi, registrasi perangkat FCM, dan umpan notifikasi.
 * Seluruh fitur lain memakai jembatan /api/m/* (lihat MobileBridge).
 */
class MobileController extends Controller
{
    private const PJU_ROLES = [
        'ka_bacadnas', 'ses_bacadnas', 'kapus_komcad', 'pembina_matra', 'pembina_kodam',
        'pembina_kodaeral', 'pembina_kodau', 'pembina_kodim', 'pembina_lanal', 'pembina_lanud',
    ];

    public function config()
    {
        $settings = Setting::all()->pluck('value', 'key');

        return response()->json([
            'success' => true,
            'app_name' => $settings['app_name'] ?? 'SISFOPERSKC',
            'app_title' => $settings['app_title'] ?? 'Sistem Informasi Personel Komponen Cadangan',
            'logo_tni' => $settings['logo_tni'] ?? null,
            'logo_ad' => $settings['logo_ad'] ?? null,
            'logo_al' => $settings['logo_al'] ?? null,
            'logo_au' => $settings['logo_au'] ?? null,
            'login_background' => $settings['login_background'] ?? null,
            'under_maintenance' => ($settings['under_maintenance'] ?? '0') === '1',
            'disable_whatsapp_otp' => ($settings['disable_whatsapp_otp'] ?? '0') === '1',
            'agora_app_id' => $settings['agora_app_id'] ?? env('AGORA_APP_ID', '19daeb63b0ec46f2b02197c9fbbe81d6'),
            'fcm_enabled' => FcmService::enabled(),
            'firebase' => FcmService::clientConfig(),
            'min_app_version' => (int) ($settings['mobile_min_version'] ?? 1),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:150',
            'password' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:120',
        ]);

        $username = trim($request->input('username'));
        $password = (string) $request->input('password');
        $deviceName = Str::limit($request->input('device_name') ?: 'Android', 100, '');

        $user = User::where('username', $username)->first()
            ?? User::where('email', strtolower($username))->first();

        if ($user && Hash::check($password, $user->password)) {
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'code' => 'inactive',
                    'message' => 'Akun Anda belum aktif. Silakan tunggu verifikasi Admin.',
                ], 403);
            }

            try {
                LoginLog::create([
                    'user_id' => $user->id,
                    'login_at' => now(),
                    'ip_address' => $request->ip(),
                    'browser' => 'Aplikasi Android',
                    'os' => 'Android',
                    'device' => Str::limit($deviceName, 45, ''),
                ]);
            } catch (\Throwable $e) {
                report($e);
            }

            $token = $user->createToken('android:' . $deviceName, ['*'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'token' => $token,
                'me' => $this->buildMe($user),
            ]);
        }

        $pju = Pju::where('email', strtolower($username))->first()
            ?? Pju::where('username', $username)->first();

        if ($pju && Hash::check($password, $pju->password)) {
            if (!$pju->is_active) {
                return response()->json([
                    'success' => false,
                    'code' => 'inactive',
                    'message' => 'Akun PJU Anda dalam status non-aktif. Silakan hubungi Administrator.',
                ], 403);
            }

            $token = $pju->createToken('android:' . $deviceName, ['*'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'token' => $token,
                'me' => $this->buildMe($pju),
            ]);
        }

        return response()->json([
            'success' => false,
            'code' => 'invalid_credentials',
            'message' => 'Kredensial login tidak cocok dengan data kami.',
            'errors' => ['username' => ['Kredensial login tidak cocok dengan data kami.']],
        ], 422);
    }

    public function me(Request $request)
    {
        $actor = $this->actor();
        if (!$actor) {
            return $this->unauthenticated();
        }

        return response()->json(['success' => true, 'me' => $this->buildMe($actor)]);
    }

    public function logout(Request $request)
    {
        $token = $request->attributes->get('mobile_token');

        if ($request->filled('fcm_token')) {
            MobileDevice::where('fcm_token', $request->input('fcm_token'))->delete();
        }
        if ($token) {
            $token->delete();
        }

        return response()->json(['success' => true, 'message' => 'Anda telah keluar.']);
    }

    public function registerDevice(Request $request)
    {
        $actor = $this->actor();
        if (!$actor) {
            return $this->unauthenticated();
        }

        $data = $request->validate([
            'fcm_token' => 'required|string|max:255',
            'platform' => 'nullable|string|max:20',
            'device_name' => 'nullable|string|max:120',
            'app_version' => 'nullable|string|max:30',
        ]);

        MobileDevice::updateOrCreate(
            ['fcm_token' => $data['fcm_token']],
            [
                'tokenable_type' => get_class($actor),
                'tokenable_id' => $actor->id,
                'platform' => $data['platform'] ?? 'android',
                'device_name' => $data['device_name'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'fcm_enabled' => FcmService::enabled()]);
    }

    public function unregisterDevice(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string|max:255']);
        MobileDevice::where('fcm_token', $request->input('fcm_token'))->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Umpan notifikasi ringan untuk polling latar belakang & daftar notifikasi aplikasi.
     */
    public function notifications(Request $request)
    {
        $actor = $this->actor();
        if (!$actor) {
            return $this->unauthenticated();
        }

        $query = DB::table('notifications')
            ->where('notifiable_type', get_class($actor))
            ->where('notifiable_id', $actor->id);

        $unread = (clone $query)->whereNull('read_at')->count();

        if ($request->filled('since')) {
            try {
                $query->where('created_at', '>', \Carbon\Carbon::parse($request->input('since')));
            } catch (\Throwable $e) {
                // abaikan parameter tidak valid
            }
        }

        $limit = min(max((int) $request->input('limit', 50), 1), 100);

        $items = $query->orderByDesc('created_at')->limit($limit)->get()->map(function ($n) {
            $data = json_decode($n->data, true) ?: [];
            return [
                'id' => $n->id,
                'title' => $data['title'] ?? 'Notifikasi',
                'message' => $data['message'] ?? '',
                'icon' => $data['icon'] ?? 'bell',
                'url' => $data['url'] ?? null,
                'read_at' => $n->read_at,
                'created_at' => \Carbon\Carbon::parse($n->created_at)->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'unread_count' => $unread,
            'server_time' => now()->toIso8601String(),
            'items' => $items,
        ]);
    }

    public function readNotification(Request $request, string $id)
    {
        $actor = $this->actor();
        if (!$actor) {
            return $this->unauthenticated();
        }

        DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_type', get_class($actor))
            ->where('notifiable_id', $actor->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function readAllNotifications(Request $request)
    {
        $actor = $this->actor();
        if (!$actor) {
            return $this->unauthenticated();
        }

        DB::table('notifications')
            ->where('notifiable_type', get_class($actor))
            ->where('notifiable_id', $actor->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ------------------------------------------------------------------

    protected function actor(): User|Pju|null
    {
        return Auth::guard('web')->user() ?: Auth::guard('pju')->user();
    }

    protected function unauthenticated()
    {
        return response()->json([
            'success' => false,
            'code' => 'unauthenticated',
            'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
        ], 401);
    }

    protected function buildMe(User|Pju $actor): array
    {
        $settings = Setting::all()->pluck('value', 'key');

        if ($actor instanceof Pju) {
            return [
                'type' => 'pju',
                'id' => $actor->id,
                'uuid' => $actor->uuid,
                'username' => $actor->username,
                'email' => $actor->email,
                'full_name' => $actor->full_name,
                'phone_number' => $actor->phone_number,
                'nrp' => $actor->nrp,
                'role' => $actor->role_pju,
                'role_group' => 'pju',
                'role_label' => $actor->jabatan_pju ?: Str::headline((string) $actor->role_pju),
                'jabatan' => $actor->jabatan_pju,
                'matra' => $actor->matra,
                'satuan_wilayah' => $actor->satuan_wilayah,
                'can_broadcast' => $actor->role_pju === 'pembina_matra',
                'gates' => ['needs_otp' => false, 'needs_sinyalmen' => false, 'needs_kewilayahan' => false],
                'personel' => null,
                'unread_notifications' => DB::table('notifications')->where('notifiable_type', Pju::class)->where('notifiable_id', $actor->id)->whereNull('read_at')->count(),
                'app' => $this->appInfo($settings),
            ];
        }

        $roleName = $actor->role?->name ?? 'personel';
        $roleGroup = match (true) {
            $roleName === 'admin' => 'admin',
            in_array($roleName, ['kordinator_angkatan', 'kordinator_matra'], true) => 'kordinator',
            $roleName === 'komandan' => 'komandan',
            in_array($roleName, self::PJU_ROLES, true) => 'pju',
            default => 'personel',
        };

        $personel = $actor->personel;
        $needsOtp = $roleName === 'personel' && (!$personel || !$personel->face_verified);
        $needsSinyalmen = $roleName === 'personel' && !$needsOtp && (!$personel || $personel->status_profile !== 'LENGKAP');
        $needsKewilayahan = $roleName === 'personel' && $personel
            && (!$personel->is_kewilayahan_updated || empty($personel->kotama) || empty($personel->satuan_kewilayahan));

        return [
            'type' => 'user',
            'id' => $actor->id,
            'uuid' => $actor->uuid,
            'username' => $actor->username,
            'email' => $actor->email,
            'full_name' => $personel?->full_name ?? Str::headline((string) $actor->username),
            'phone_number' => $personel?->phone_number,
            'nrp' => null,
            'role' => $roleName,
            'role_group' => $roleGroup,
            'role_label' => $actor->role?->display_name ?? Str::headline($roleName),
            'jabatan' => null,
            'matra' => $personel?->matra,
            'satuan_wilayah' => $personel?->satuan_kewilayahan,
            'can_broadcast' => in_array($roleGroup, ['admin', 'kordinator'], true),
            'gates' => [
                'needs_otp' => $needsOtp,
                'needs_sinyalmen' => $needsSinyalmen,
                'needs_kewilayahan' => (bool) $needsKewilayahan,
            ],
            'personel' => $personel ? [
                'id' => $personel->id,
                'uuid' => $personel->uuid,
                'full_name' => $personel->full_name,
                'pangkat' => $personel->pangkat,
                'pangkat_label' => Personel::formatLongRank($personel->pangkat),
                'nikc' => $personel->nikc,
                'nik' => $personel->nik,
                'matra' => $personel->matra,
                'angkatan' => $personel->angkatan,
                'gender' => $personel->gender,
                'pob' => $personel->pob,
                'dob' => optional($personel->dob)->toDateString() ?? (string) $personel->dob,
                'phone_number' => $personel->phone_number,
                'address' => $personel->address,
                'province' => $personel->province,
                'city' => $personel->city,
                'photo_profile' => $personel->photo_profile,
                'kotama' => $personel->kotama,
                'satuan_kewilayahan' => $personel->satuan_kewilayahan,
                'status_keaktifan' => $personel->status_keaktifan,
                'face_verified' => (bool) $personel->face_verified,
                'status_profile' => $personel->status_profile,
                'is_asn' => (bool) $personel->is_asn,
            ] : null,
            'unread_notifications' => DB::table('notifications')->where('notifiable_type', User::class)->where('notifiable_id', $actor->id)->whereNull('read_at')->count(),
            'app' => $this->appInfo($settings),
        ];
    }

    protected function appInfo($settings): array
    {
        return [
            'app_name' => $settings['app_name'] ?? 'SISFOPERSKC',
            'logo_tni' => $settings['logo_tni'] ?? null,
            'agora_app_id' => $settings['agora_app_id'] ?? env('AGORA_APP_ID', '19daeb63b0ec46f2b02197c9fbbe81d6'),
            'fcm_enabled' => FcmService::enabled(),
            'firebase' => FcmService::clientConfig(),
        ];
    }
}
