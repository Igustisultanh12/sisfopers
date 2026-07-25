<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $unreadCount = $user->unreadNotifications()->count();
        $notifications = $user->notifications()
            ->latest()
            ->paginate(15);

        // Transform data map
        $transformed = collect($notifications->items())->map(function ($notif) {
            return [
                'id' => $notif->id,
                'title' => $notif->data['title'] ?? 'Pemberitahuan',
                'message' => $notif->data['message'] ?? '',
                'icon' => $notif->data['icon'] ?? 'bell',
                'url' => $notif->data['url'] ?? null,
                'read_at' => $notif->read_at ? $notif->read_at->toDateTimeString() : null,
                'created_at' => $notif->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => [
                'current_page' => $notifications->currentPage(),
                'data' => $transformed,
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total()
            ]
        ]);
    }

    public function readAll(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil ditandai sebagai telah dibaca.'
        ]);
    }
}
