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
    private function getPjuUser(Request $request)
    {
        return $request->user('pju') ?? $request->user() ?? \Illuminate\Support\Facades\Auth::guard('pju')->user();
    }

    public function index(Request $request)
    {
        $user = $this->getPjuUser($request);
        $matra = $user ? $user->matra : null;
        $satuan = $user ? $user->satuan_wilayah : null;

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
        $user = $this->getPjuUser($request);
        $query = Personel::with(['user', 'sinyalmen', 'riwayatPendidikan', 'jobHistories', 'broadcastResponses.broadcast']);

        if ($user && $user->matra) {
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

        if ($request->filled('matra') && (!$user || !$user->matra)) {
            $query->where('matra', $request->matra);
        }

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        $personels = $query->paginate(15)->withQueryString();

        $roleName = $user ? (is_object($user->role) ? $user->role->name : ($user->role_pju ?? 'pju')) : 'pju';

        return Inertia::render('Pju/Personel', [
            'personels' => $personels,
            'filters'   => $request->only(['search', 'matra', 'angkatan']),
            'userRole'  => $roleName,
        ]);
    }

    public function broadcastIndex(Request $request)
    {
        $user = $this->getPjuUser($request);
        $query = Broadcast::with(['creator', 'responses']);

        if ($user && $user->matra) {
            $query->where(function($q) use ($user) {
                $q->whereNull('matra')->orWhere('matra', $user->matra);
            });
        }

        $broadcasts = $query->latest()->paginate(10)->withQueryString();
        $roleName = $user ? (is_object($user->role) ? $user->role->name : ($user->role_pju ?? '')) : '';

        return Inertia::render('Pju/Broadcast/Index', [
            'broadcasts' => $broadcasts,
            'canCreate'  => in_array($roleName, ['pembina_matra', 'admin']),
        ]);
    }

    public function broadcastCreate(Request $request)
    {
        $user = $this->getPjuUser($request);
        $roleName = $user ? (is_object($user->role) ? $user->role->name : ($user->role_pju ?? '')) : '';
        abort_unless(in_array($roleName, ['pembina_matra', 'admin']), 403, 'Akses terbatas untuk Pembina Matra dan Admin.');

        return Inertia::render('Pju/Broadcast/Create', [
            'userMatra' => $user->matra ?? null,
        ]);
    }

    public function broadcastStore(Request $request)
    {
        $user = $this->getPjuUser($request);
        $roleName = $user ? (is_object($user->role) ? $user->role->name : ($user->role_pju ?? '')) : '';
        abort_unless(in_array($roleName, ['pembina_matra', 'admin']), 403, 'Akses terbatas untuk Pembina Matra dan Admin.');

        $validated = $request->validate([
            'title'      => 'required|string|max:200',
            'content'    => 'required|string',
            'category'   => 'required|string',
            'event_date' => 'nullable|date',
            'matra'      => 'nullable|in:AD,AL,AU',
        ]);

        $broadcast = Broadcast::create([
            'uuid'         => (string) \Illuminate\Support\Str::uuid(),
            'created_by'   => $user ? $user->id : null,
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'category'     => $validated['category'],
            'event_date'   => $validated['event_date'] ?? null,
            'matra'        => ($user && $user->matra) ? $user->matra : ($validated['matra'] ?? null),
            'target_scope' => ($user && $user->matra) ? 'MATRA' : 'SEMUA',
            'status'       => 'PUBLISHED',
        ]);

        try {
            $personelQuery = Personel::query();
            if ($broadcast->matra) {
                $personelQuery->where('matra', $broadcast->matra);
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
