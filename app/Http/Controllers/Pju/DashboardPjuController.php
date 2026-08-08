<?php

namespace App\Http\Controllers\Pju;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\Broadcast;
use App\Models\BroadcastResponse;
use App\Models\MasterKepangkatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardPjuController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $matra = $user->matra;
        $satuan = $user->satuan_wilayah;

        $query = Personel::query();
        if ($matra) {
            $query->where('matra', $matra);
        }

        $totalPersonel = (clone $query)->count();
        $personelAd = Personel::where('matra', 'AD')->count();
        $personelAl = Personel::where('matra', 'AL')->count();
        $personelAu = Personel::where('matra', 'AU')->count();

        // Broadcast stats
        $broadcastQuery = Broadcast::query();
        if ($matra) {
            $broadcastQuery->where(function($q) use ($matra) {
                $q->whereNull('matra')->orWhere('matra', $matra);
            });
        }
        $totalBroadcast = $broadcastQuery->count();

        return Inertia::render('Pju/Dashboard', [
            'user' => $user,
            'stats' => [
                'total_personel' => $totalPersonel,
                'personel_ad'    => $personelAd,
                'personel_al'    => $personelAl,
                'personel_au'    => $personelAu,
                'total_broadcast' => $totalBroadcast,
            ],
        ]);
    }

    public function personelIndex(Request $request)
    {
        $user = $request->user();
        $query = Personel::with(['user', 'sinyalmen', 'riwayatPendidikan']);

        if ($user->matra) {
            $query->where('matra', $user->matra);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nikc', 'like', "%{$search}%");
            });
        }

        if ($request->filled('matra') && !$user->matra) {
            $query->where('matra', $request->matra);
        }

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        $personels = $query->paginate(15)->withQueryString();

        return Inertia::render('Pju/Personel', [
            'personels' => $personels,
            'filters'   => $request->only(['search', 'matra', 'angkatan']),
            'userRole'  => $user->role ? $user->role->name : 'pju',
        ]);
    }

    public function broadcastIndex(Request $request)
    {
        $user = $request->user();
        $query = Broadcast::with(['creator', 'responses']);

        if ($user->matra) {
            $query->where(function($q) use ($user) {
                $q->whereNull('matra')->orWhere('matra', $user->matra);
            });
        }

        $broadcasts = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pju/Broadcast/Index', [
            'broadcasts' => $broadcasts,
            'canCreate'  => in_array($user->role ? $user->role->name : '', ['pembina_matra', 'admin']),
        ]);
    }

    public function broadcastCreate(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role ? $user->role->name : '', ['pembina_matra', 'admin']), 403, 'Akses terbatas untuk Pembina Matra dan Admin.');

        return Inertia::render('Pju/Broadcast/Create', [
            'userMatra' => $user->matra,
        ]);
    }

    public function broadcastStore(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role ? $user->role->name : '', ['pembina_matra', 'admin']), 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'content'     => 'required|string',
            'target_type' => 'required|in:ALL,MATRA,ANGKATAN',
            'matra'       => 'nullable|in:AD,AL,AU',
            'angkatan'    => 'nullable|digits:4',
        ]);

        $broadcast = Broadcast::create([
            'uuid'        => \Illuminate\Support\Str::uuid(),
            'sender_id'   => $user->id,
            'title'       => $validated['title'],
            'content'     => $validated['content'],
            'target_type' => $validated['target_type'],
            'matra'       => $validated['matra'] ?? $user->matra,
            'angkatan'    => $validated['angkatan'] ?? null,
            'sent_at'     => now(),
        ]);

        // Kirim Notifikasi WA ke Personel Jajaran
        try {
            $personelQuery = Personel::query();
            if ($broadcast->matra) {
                $personelQuery->where('matra', $broadcast->matra);
            }
            if ($broadcast->angkatan) {
                $personelQuery->where('angkatan', $broadcast->angkatan);
            }

            $personels = $personelQuery->get();

            foreach ($personels as $pers) {
                if ($pers->phone_number) {
                    $msg = "*INFORMASI BROADCAST KEGIATAN KOMCAD*\n\n"
                        . "Judul: *{$broadcast->title}*\n\n"
                        . "{$broadcast->content}\n\n"
                        . "Silakan login ke SISFOPERS untuk melakukan konfirmasi kehadiran.";
                    \App\Services\WhatsappService::sendMessage($pers->phone_number, $msg);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal notifikasi WA Broadcast PJU: " . $e->getMessage());
        }

        return redirect()->route('pju.broadcast.index')->with('success', 'Pesan broadcast kegiatan berhasil dikirimkan ke jajaran.');
    }
}
