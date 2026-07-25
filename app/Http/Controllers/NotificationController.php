<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Menampilkan semua daftar notifikasi milik user yang sedang login
     */
    public function index()
    {
        $user = Auth::user();
        
        // Mengambil semua notifikasi dengan paginasi
        $notifications = $user->notifications()->paginate(15);

        // Menentukan layout berdasarkan role user agar navbar/sidebar tetap konsisten
        $component = 'Notification/Index';
        if ($user->hasRole('admin')) {
            $prefix = 'Admin';
        } elseif ($user->hasRole('komandan')) {
            $prefix = 'Komandan';
        } else {
            $prefix = 'Personel';
        }

        return Inertia::render('Notification/Index', [
            'notifications' => $notifications,
            'rolePrefix' => $prefix
        ]);
    }

    /**
     * Menandai notifikasi tertentu sebagai "Sudah Dibaca" dan mengarahkan ke rute tujuan
     */
    public function read($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Mengambil target URL tujuan dari data payload notifikasi
        $targetUrl = $notification->data['url'] ?? null;

        if ($targetUrl) {
            return redirect($targetUrl);
        }

        return redirect()->back();
    }

    /**
     * Menandai semua notifikasi milik user sebagai "Sudah Dibaca"
     */
    public function readAll()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('success', 'Semua notifikasi berhasil dibaca.');
    }
}