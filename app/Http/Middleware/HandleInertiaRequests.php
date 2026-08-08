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
        $personel = $user ? $user->getPersonelOrAutoCreate() : null;
        $needKewilayahanUpdate = false;
        if ($user && $user->role && $user->role->name === 'personel' && $personel) {
            $needKewilayahanUpdate = (!$personel->is_kewilayahan_updated || empty($personel->kotama) || empty($personel->satuan_kewilayahan));
        }

        return [
            ...parent::share($request),
            
            // MAP DATA AUTH SECARA STRICT DAN AMAN UNTUK MEMBENTENGIN LAYOUT VUE
            'auth' => [
                'user' => $user ? [
                    'id'               => $user->id,
                    'username'         => $user->username,
                    'email'            => $user->email,
                    'whatsapp_number'  => $user->whatsapp_number,
                    'role'             => $user->role ? [
                        'id'   => $user->role->id,
                        'name' => $user->role->name,
                    ] : null,
                    'email_verified'   => $user->hasVerifiedEmail(), 
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
            'settings' => \App\Models\Setting::all()->pluck('value', 'key')->toArray(),
        ];
    }
}