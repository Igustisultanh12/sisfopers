<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\Broadcast;
use App\Models\BroadcastResponse;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardAdminController extends Controller
{
    public function index()
    {
        // 1. Data Agregasi Widget Utama
        $stats = [
            'total_personel' => Personel::count(),
            'personel_aktif' => Personel::whereHas('user', function($q) { $q->where('is_active', true); })->count(),
            'pending_verification' => Personel::whereHas('registration', function($q) { $q->where('status_verification', 'PENDING'); })->count(),
            'broadcast_aktif' => Broadcast::where('deadline', '>', now())->count(),
            'broadcast_selesai' => Broadcast::where('deadline', '<=', now())->count(),
            'login_hari_ini' => LoginLog::whereDate('login_at', today())->count(),
            'respon_hadir' => BroadcastResponse::where('status_attendance', 'HADIR')->count(),
            'respon_izin' => BroadcastResponse::where('status_attendance', 'IZIN')->count(),
        ];

        // 2. Data Grafik Distribusi Matra (AD, AL, AU)
        $matraChart = Personel::select('matra', DB::raw('count(*) as total'))
            ->groupBy('matra')
            ->get()
            ->pluck('total', 'matra')->toArray();

        // 3. Data Grafik Aktivitas Login (7 Hari Terakhir)
        $loginTrend = LoginLog::select(DB::raw('DATE(login_at) as date'), DB::raw('count(*) as total'))
            ->where('login_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // 4. Log Aktivitas & Verifikasi Terbaru
        $recentActivities = DB::table('audit_logs')
            ->join('users', 'audit_logs.user_id', '=', 'users.id')
            ->select('audit_logs.*', 'users.username')
            ->orderBy('audit_logs.created_at', 'DESC')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'charts' => [
                'matra' => [
                    'labels' => ['TNI AD', 'TNI AL', 'TNI AU'],
                    'data' => [
                        $matraChart['AD'] ?? 0,
                        $matraChart['AL'] ?? 0,
                        $matraChart['AU'] ?? 0
                    ]
                ],
                'trend' => [
                    'labels' => $loginTrend->pluck('date')->map(fn($d) => date('d M', strtotime($d))),
                    'data' => $loginTrend->pluck('total')
                ]
            ],
            'recentActivities' => $recentActivities
        ]);
    }
}