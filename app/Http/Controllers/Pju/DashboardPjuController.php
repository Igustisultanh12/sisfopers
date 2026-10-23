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

        // Kolom broadcasts mengikuti skema utama (event_time, location, deadline, target).
        $category = strtolower((string) $validated['category']);
        if (!in_array($category, ['latihan', 'apel', 'mobilisasi', 'pengumuman', 'tugas'], true)) {
            $category = 'pengumuman';
        }
        $eventDate = !empty($validated['event_date']) ? \Carbon\Carbon::parse($validated['event_date']) : now();
        $matra = ($user && $user->matra) ? $user->matra : ($validated['matra'] ?? null);
        $creatorId = ($user instanceof \App\Models\User)
            ? $user->id
            : \App\Models\User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->orderBy('id')->value('id');

        $broadcast = Broadcast::create([
            'uuid'         => (string) \Illuminate\Support\Str::uuid(),
            'created_by'   => $creatorId,
            'title'        => $validated['title'],
            'category'     => $category,
            'event_date'   => $eventDate->toDateString(),
            'event_time'   => $request->input('event_time') ?: '08:00',
            'location'     => $request->input('location') ?: (($user->satuan_wilayah ?? null) ?: '-'),
            'description'  => $validated['content'],
            'deadline'     => $eventDate->copy()->endOfDay(),
            'target_type'  => $matra ? 'MATRA' : 'ALL',
            'target_value' => $matra,
            'matra'        => $matra ?: 'ALL',
        ]);

        try {
            $targets = Personel::with('user')->when($matra, fn ($q) => $q->where('matra', $matra))->get();
            foreach ($targets as $pers) {
                if ($pers->user) {
                    $pers->user->notify(new \App\Notifications\NewBroadcastNotification($broadcast));
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notifikasi broadcast PJU gagal: ' . $e->getMessage());
        }

        try {
            $personelQuery = Personel::query();
            if ($matra) {
                $personelQuery->where('matra', $matra);
            }
            $personels = $personelQuery->get();

            foreach ($personels as $pers) {
                if ($pers->phone_number) {
                    $msg = "*INFORMASI BROADCAST KEGIATAN KOMCAD*\n\n"
                        . "Judul: *{$broadcast->title}*\n\n"
                        . "{$broadcast->description}\n\n"
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
