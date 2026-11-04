<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        if (!$user && \Illuminate\Support\Facades\Auth::guard('pju')->check()) {
            $user = \Illuminate\Support\Facades\Auth::guard('pju')->user();
        }

        if ($user && method_exists($user, 'unsetRelation')) {
            $user->unsetRelation('personel');
        }
        $personel = ($user && method_exists($user, 'getPersonelOrAutoCreate')) ? $user->getPersonelOrAutoCreate() : null;
        $needKewilayahanUpdate = false;
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('personel') && $personel) {
            $needKewilayahanUpdate = (!$personel->is_kewilayahan_updated || empty($personel->kotama) || empty($personel->satuan_kewilayahan));
        }

        // Foto pengguna untuk layout: avatar unggahan akun (disk publik), atau pasfoto
        // personel (disk privat, dilayani private-stream dengan cek kepemilikan).
        $avatarUrl = null;
        if ($user && !empty($user->avatar)) {
            $avatarUrl = asset('storage/' . $user->avatar);
        } elseif ($personel && !empty($personel->photo_profile)) {
            $prefix = $request->is('api/m/*') ? '/api/m' : '';
            $avatarUrl = url($prefix . '/documents/private-stream?path=' . urlencode($personel->photo_profile));
        }

        return [
            ...parent::share($request),

            // MAP DATA AUTH SECARA STRICT DAN AMAN UNTUK MEMBENTENGIN LAYOUT VUE
            'auth' => [
                'user' => $user ? [
                    'id'               => $user->id,
                    'username'         => $user->username,
                    'full_name'        => $user->full_name ?? $user->username,
                    'email'            => $user->email,
                    'avatar_url'       => $avatarUrl,
                    'whatsapp_number'  => $user->phone_number ?? $user->whatsapp_number ?? '-',
                    'jabatan_pju'      => $user->jabatan_pju ?? null,
                    'satuan_wilayah'   => $user->satuan_wilayah ?? null,
                    'role'             => (object)[
                        'name' => is_object($user->role) ? $user->role->name : ($user->role_pju ?? 'pju'),
                    ],
                    'email_verified'   => method_exists($user, 'hasVerifiedEmail') ? $user->hasVerifiedEmail() : true, 
                ] : null,

                'personel' => $personel ? [
                    'id'                     => $personel->id,
                    'full_name'              => $personel->full_name,
                    'matra'                  => $personel->matra,
                    'kotama'                 => $personel->kotama,
                    'satuan_kewilayahan'     => $personel->satuan_kewilayahan,
                    'is_kewilayahan_updated' => $personel->is_kewilayahan_updated,
                ] : null,

                'needKewilayahanUpdate' => $needKewilayahanUpdate,

                // HUB SYNC NOTIFIKASI GLOBAL (Bypass Cache Proxy Tunnel via Direct DB Query)
                'unread_notifications_count' => $user ? DB::table('notifications')->where('notifiable_id', $user->id)->whereNull('read_at')->count() : 0,
                'all_notifications'          => $user ? DB::table('notifications')->where('notifiable_id', $user->id)->latest()->take(10)->get()->map(function ($notif) {
                    return [
                        'id'         => $notif->id,
                        'type'       => $notif->type,
                        'data'       => json_decode($notif->data, true), // Wajib di-decode agar payload JSON terbaca reaktif di Vue
                        'read_at'    => $notif->read_at,
                        'created_at' => $notif->created_at,
                    ];
                })->toArray() : [],
            ],

            // FLASH SESSION TRACKER (ROMEI SaaS System Style)
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
                'message' => $request->session()->get('message'),
            ],

            // PARAMETER GLOBAL SETTING
            // Non-admin & guest hanya menerima allowlist key tampilan publik.
            // Admin menerima seluruh setting KECUALI key rahasia yang tetap tak pernah
            // dikirim ke klien (gunakan endpoint admin khusus bila perlu nilainya).
            'settings' => function () use ($user) {
                $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();

                // Key rahasia tidak pernah dibagikan ke klien (bahkan admin) via shared props.
                $secretKeys = [
                    'turnstile_secret_key', 'wa_api_key', 'coturn_secret',
                    'metered_api_key', 'agora_app_certificate',
                    'mail_password', 'mail_username',
                ];
                foreach ($secretKeys as $sk) {
                    unset($settings[$sk]);
                }

                $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
                if ($isAdmin) {
                    return $settings;
                }

                // Allowlist key tampilan publik untuk non-admin / guest.
                $publicKeys = [
                    'app_name', 'app_title', 'footer_text', 'favicon',
                    'login_background', 'logo_tni', 'logo_ad', 'logo_al', 'logo_au',
                    'turnstile_enabled', 'turnstile_site_key', 'agora_app_id',
                    'under_maintenance', 'disable_whatsapp_otp',
                ];
                return array_intersect_key($settings, array_flip($publicKeys));
            },
        ];
    }
}