<?php

namespace App\Http\Controllers\Vicon;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\Setting;
use App\Models\User;
use App\Models\ViconMessage;
use App\Models\ViconParticipant;
use App\Models\ViconRoom;
use App\Services\AgoraTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ViconController extends Controller
{
    /**
     * Dashboard Vicon & Rapat Dinas (Admin / Pengelola)
     */
    public function adminIndex(Request $request)
    {
        $user = Auth::user();

        $activeRooms = ViconRoom::with(['host', 'participants'])
            ->where('status', 'ACTIVE')
            ->orderBy('started_at', 'desc')
            ->get();

        $scheduledRooms = ViconRoom::with(['host', 'participants'])
            ->where('status', 'SCHEDULED')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $pastRooms = ViconRoom::with(['host'])
            ->where('status', 'ENDED')
            ->orderBy('ended_at', 'desc')
            ->paginate(15);

        // Daftar personel untuk modal pemilihan undangan
        $personels = Personel::select('id', 'user_id', 'full_name', 'pangkat', 'nikc', 'matra', 'angkatan', 'photo_profile')
            ->orderBy('full_name')
            ->limit(500)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'user_id' => $p->user_id,
                    'full_name' => $p->full_name,
                    'pangkat' => Personel::formatLongRank($p->pangkat),
                    'nikc' => $p->nikc,
                    'matra' => $p->matra,
                    'kompi' => $p->angkatan ? "Angkatan {$p->angkatan}" : 'Komcad',
                    'label' => $p->angkatan ? "Angkatan {$p->angkatan}" : 'Komcad',
                    'photo' => $p->photo_profile,
                ];
            });

        return Inertia::render('Admin/Vicon/Index', [
            'activeRooms' => $activeRooms,
            'scheduledRooms' => $scheduledRooms,
            'pastRooms' => $pastRooms,
            'personels' => $personels,
            'currentRole' => $user->hasRole('admin') ? 'admin' : ($user->hasRole('pju') ? 'pju' : 'kordinator'),
        ]);
    }

    /**
     * Membuat Ruang Rapat Dinas Baru
     */
    public function storeRoom(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'allow_guest' => 'boolean',
            'guest_passcode' => 'nullable|string|max:30',
            'is_scheduled' => 'boolean',
            'scheduled_at' => 'nullable|date',
            'invited_personel_ids' => 'nullable|array',
            'invited_personel_ids.*' => 'integer|exists:personels,id',
        ]);

        $uuid = (string) Str::uuid();
        $cleanId = preg_replace('/[^a-zA-Z0-9]/', '', $uuid);
        $roomCode = 'VICON-KC-' . strtoupper(Str::random(6));
        $agoraChannel = 'VICON_' . $cleanId;

        $user = Auth::user();
        $isScheduled = $request->boolean('is_scheduled');

        $room = ViconRoom::create([
            'uuid' => $uuid,
            'room_code' => $roomCode,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'host_user_id' => $user->id,
            'status' => $isScheduled ? 'SCHEDULED' : 'ACTIVE',
            'agora_channel' => $agoraChannel,
            'allow_guest' => $request->boolean('allow_guest', true),
            'guest_passcode' => $request->input('guest_passcode') ?: null,
            'max_participants' => 50,
            'scheduled_at' => $isScheduled ? $request->input('scheduled_at') : null,
            'started_at' => $isScheduled ? null : now(),
        ]);

        // Daftarkan Host sebagai partisipan utama
        $hostRank = $user->personel ? Personel::formatLongRank($user->personel->pangkat) : '';
        $hostName = trim("{$hostRank} " . ($user->personel ? $user->personel->full_name : ($user->name ?? 'Admin Dinas')));

        ViconParticipant::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'personel_id' => $user->personel?->id,
            'display_name' => "{$hostName} (Host)",
            'role' => 'HOST',
            'agora_uid' => (int) $user->id,
            'status' => 'JOINED',
            'invited_at' => now(),
            'joined_at' => now(),
        ]);

        // Daftarkan Personel yang diundang
        $invitedIds = $request->input('invited_personel_ids', []);
        if (!empty($invitedIds)) {
            $personels = Personel::whereIn('id', $invitedIds)->get();
            foreach ($personels as $pers) {
                $rank = Personel::formatLongRank($pers->pangkat);
                $persName = "{$rank} {$pers->full_name}";
                $uid = (int) ($pers->user_id ?: (100000 + $pers->id));

                ViconParticipant::create([
                    'room_id' => $room->id,
                    'user_id' => $pers->user_id,
                    'personel_id' => $pers->id,
                    'display_name' => $persName,
                    'role' => 'PERSONEL',
                    'agora_uid' => $uid,
                    'status' => 'INVITED',
                    'invited_at' => now(),
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'room' => $room,
                'redirect' => route('admin.vicon.room', $room->uuid),
            ]);
        }

        return redirect()->route('admin.vicon.room', $room->uuid)->with('success', 'Ruang rapat dinas berhasil diinisiasi.');
    }

    /**
     * Ruang Konferensi Video Admin / Host
     */
    public function adminRoom(Request $request, $uuid)
    {
        $user = Auth::user();
        $room = ViconRoom::with(['host', 'participants.personel', 'messages'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Jika rapat terjadwal dan dibuka oleh Host, aktifkan menjadi ACTIVE
        if ($room->status === 'SCHEDULED' && $room->host_user_id === $user->id) {
            $room->update([
                'status' => 'ACTIVE',
                'started_at' => now(),
            ]);
        }

        // Pastikan akun saat ini tercatat di daftar partisipan
        $participant = ViconParticipant::where('room_id', $room->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$participant) {
            $rank = $user->personel ? Personel::formatLongRank($user->personel->pangkat) : '';
            $name = trim("{$rank} " . ($user->personel ? $user->personel->full_name : ($user->name ?? 'Admin Dinas')));
            $participant = ViconParticipant::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'personel_id' => $user->personel?->id,
                'display_name' => $name,
                'role' => ($room->host_user_id === $user->id) ? 'HOST' : 'ADMIN',
                'agora_uid' => (int) $user->id,
                'status' => 'JOINED',
                'joined_at' => now(),
            ]);
        } else {
            $participant->update([
                'status' => 'JOINED',
                'joined_at' => now(),
            ]);
        }

        $appId = Setting::where('key', 'agora_app_id')->value('value') ?: env('AGORA_APP_ID', '19daeb63b0ec46f2b02197c9fbbe81d6');

        return Inertia::render('Admin/Vicon/Room', [
            'room' => $room,
            'currentParticipant' => $participant,
            'agoraAppId' => $appId,
            'isHost' => ($room->host_user_id === $user->id),
        ]);
    }

    /**
     * Mengundang Personel Tambahan ke Sesi Rapat yang Sedang Berjalan
     */
    public function invitePersonel(Request $request, $uuid)
    {
        $room = ViconRoom::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'personel_ids' => 'required|array',
            'personel_ids.*' => 'integer|exists:personels,id',
        ]);

        $personelIds = $request->input('personel_ids');
        $personels = Personel::whereIn('id', $personelIds)->get();

        $added = [];
        foreach ($personels as $pers) {
            $exists = ViconParticipant::where('room_id', $room->id)
                ->where('personel_id', $pers->id)
                ->exists();

            if (!$exists) {
                $rank = Personel::formatLongRank($pers->pangkat);
                $persName = "{$rank} {$pers->full_name}";
                $uid = (int) ($pers->user_id ?: (100000 + $pers->id));

                $p = ViconParticipant::create([
                    'room_id' => $room->id,
                    'user_id' => $pers->user_id,
                    'personel_id' => $pers->id,
                    'display_name' => $persName,
                    'role' => 'PERSONEL',
                    'agora_uid' => $uid,
                    'status' => 'INVITED',
                    'invited_at' => now(),
                ]);
                $added[] = $p;
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($added) . ' personel berhasil diundang ke ruang rapat.',
            'participants' => $added,
        ]);
    }

    /**
     * Mengakhiri Sesi Rapat Dinas untuk Seluruh Peserta
     */
    public function endRoom(Request $request, $uuid)
    {
        $user = Auth::user();
        $room = ViconRoom::where('uuid', $uuid)->firstOrFail();

        if ($room->host_user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json(['error' => 'Hanya Host atau Admin yang berhak mengakhiri rapat dinas.'], 403);
        }

        $room->update([
            'status' => 'ENDED',
            'ended_at' => now(),
        ]);

        ViconParticipant::where('room_id', $room->id)
            ->where('status', 'JOINED')
            ->update([
                'status' => 'LEFT',
                'left_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Rapat dinas telah resmi diakhiri.',
        ]);
    }

    /**
     * Menghapus Riwayat Rapat Dinas
     */
    public function destroyRoom(Request $request, $uuid)
    {
        $user = Auth::user();
        $room = ViconRoom::where('uuid', $uuid)->firstOrFail();

        if ($room->host_user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $room->delete();

        return redirect()->route('admin.vicon.index')->with('success', 'Catatan rapat dinas berhasil dihapus.');
    }

    /**
     * Halaman Daftar Vicon Dinas untuk Personel Komcad
     */
    public function personelIndex(Request $request)
    {
        $user = Auth::user();
        $personel = $user->personel;

        $roomIds = ViconParticipant::where(function ($q) use ($user, $personel) {
            $q->where('user_id', $user->id);
            if ($personel) {
                $q->orWhere('personel_id', $personel->id);
            }
        })->pluck('room_id');

        $activeRooms = ViconRoom::with(['host', 'participants'])
            ->whereIn('id', $roomIds)
            ->where('status', 'ACTIVE')
            ->orderBy('started_at', 'desc')
            ->get();

        $scheduledRooms = ViconRoom::with(['host', 'participants'])
            ->whereIn('id', $roomIds)
            ->where('status', 'SCHEDULED')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $pastRooms = ViconRoom::with(['host'])
            ->whereIn('id', $roomIds)
            ->where('status', 'ENDED')
            ->orderBy('ended_at', 'desc')
            ->paginate(10);

        return Inertia::render('Personel/Vicon/Index', [
            'activeRooms' => $activeRooms,
            'scheduledRooms' => $scheduledRooms,
            'pastRooms' => $pastRooms,
        ]);
    }

    /**
     * Ruang Konferensi Video Personel Komcad
     */
    public function personelRoom(Request $request, $uuid)
    {
        $user = Auth::user();
        $personel = $user->personel;

        $room = ViconRoom::with(['host', 'participants.personel', 'messages'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($room->status === 'ENDED') {
            return redirect()->route('personel.vicon.index')->with('error', 'Sesi rapat dinas ini telah berakhir.');
        }

        $participant = ViconParticipant::where('room_id', $room->id)
            ->where(function ($q) use ($user, $personel) {
                $q->where('user_id', $user->id);
                if ($personel) {
                    $q->orWhere('personel_id', $personel->id);
                }
            })->first();

        if (!$participant) {
            $rank = $personel ? Personel::formatLongRank($personel->pangkat) : '';
            $name = trim("{$rank} " . ($personel ? $personel->full_name : $user->name));
            $participant = ViconParticipant::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'personel_id' => $personel?->id,
                'display_name' => $name,
                'role' => 'PERSONEL',
                'agora_uid' => (int) $user->id,
                'status' => 'JOINED',
                'joined_at' => now(),
            ]);
        } else {
            $participant->update([
                'status' => 'JOINED',
                'joined_at' => now(),
            ]);
        }

        $appId = Setting::where('key', 'agora_app_id')->value('value') ?: env('AGORA_APP_ID', '19daeb63b0ec46f2b02197c9fbbe81d6');

        return Inertia::render('Personel/Vicon/Room', [
            'room' => $room,
            'currentParticipant' => $participant,
            'agoraAppId' => $appId,
        ]);
    }

    /**
     * Halaman Sambutan / Lobby Tamu Luar (Publik Tanpa Autentikasi)
     */
    public function guestJoinView(Request $request, $code = null)
    {
        $code = $code ?: $request->query('code');

        if (!$code) {
            return Inertia::render('Vicon/GuestJoin', [
                'room' => null,
                'errorMessage' => null,
            ]);
        }

        $room = ViconRoom::where('room_code', $code)
            ->orWhere('uuid', $code)
            ->first();

        if (!$room) {
            return Inertia::render('Vicon/GuestJoin', [
                'room' => null,
                'errorMessage' => 'Ruang rapat dinas dengan kode "' . e($code) . '" tidak ditemukan atau telah ditutup.',
            ]);
        }

        if (!$room->allow_guest) {
            return Inertia::render('Vicon/GuestJoin', [
                'room' => null,
                'errorMessage' => 'Ruang rapat dinas ini bersifat tertutup dan tidak menerima tamu eksternal.',
            ]);
        }

        return Inertia::render('Vicon/GuestJoin', [
            'room' => [
                'title' => $room->title,
                'room_code' => $room->room_code,
                'description' => $room->description,
                'status' => $room->status,
                'has_passcode' => !empty($room->guest_passcode),
                'started_at' => $room->started_at,
                'scheduled_at' => $room->scheduled_at,
            ],
            'errorMessage' => null,
        ]);
    }

    /**
     * Memproses Masuknya Tamu Luar ke Ruang Rapat
     */
    public function guestJoinProcess(Request $request, $code = null)
    {
        $code = $code ?: ($request->input('room_code') ?: $request->input('code'));

        if (!$code) {
            return response()->json(['error' => 'Kode ruang rapat dinas wajib disertakan.'], 422);
        }

        $room = ViconRoom::where('room_code', $code)
            ->orWhere('uuid', $code)
            ->first();

        if (!$room) {
            return response()->json(['error' => 'Ruang rapat dinas tidak ditemukan atau telah ditutup.'], 404);
        }

        if (!$room->allow_guest) {
            return response()->json(['error' => 'Akses tamu luar dinonaktifkan untuk rapat ini.'], 403);
        }

        if ($room->status === 'ENDED') {
            return response()->json(['error' => 'Rapat dinas ini telah resmi ditutup.'], 410);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'institution' => 'nullable|string|max:100',
            'passcode' => 'nullable|string',
        ]);

        if (!empty($room->guest_passcode)) {
            if ($request->input('passcode') !== $room->guest_passcode) {
                return response()->json(['error' => 'Kata sandi ruang rapat tidak sesuai.'], 422);
            }
        }

        $guestToken = Str::random(40);
        // UID numerik unik untuk tamu luar dalam rentang 800000 - 899999
        $agoraUid = 800000 + rand(1000, 99999);

        $name = trim($request->input('name'));
        $inst = trim((string) $request->input('institution'));
        $displayName = $inst !== '' ? "{$name} ({$inst})" : "{$name} (Tamu Luar)";

        $participant = ViconParticipant::create([
            'room_id' => $room->id,
            'display_name' => $displayName,
            'role' => 'GUEST',
            'agora_uid' => $agoraUid,
            'guest_token' => $guestToken,
            'status' => 'JOINED',
            'invited_at' => now(),
            'joined_at' => now(),
        ]);

        Session::put("vicon_guest_{$room->room_code}", $guestToken);

        return response()->json([
            'success' => true,
            'redirect' => route('vicon.guest.room', ['code' => $room->room_code, 'token' => $guestToken]),
        ]);
    }

    /**
     * Ruang Konferensi Video Tamu Luar (Tampilan Mandiri Imersif)
     */
    public function guestRoomView(Request $request, $code)
    {
        $room = ViconRoom::where('room_code', $code)
            ->orWhere('uuid', $code)
            ->firstOrFail();

        $token = $request->query('token') ?: Session::get("vicon_guest_{$room->room_code}");

        if (!$token) {
            return redirect()->route('vicon.guest.join', $room->room_code)->with('error', 'Sesi tamu tidak ditemukan. Silakan isi nama terlebih dahulu.');
        }

        $participant = ViconParticipant::where('room_id', $room->id)
            ->where('guest_token', $token)
            ->firstOrFail();

        $participant->update([
            'status' => 'JOINED',
            'joined_at' => now(),
        ]);

        $appId = Setting::where('key', 'agora_app_id')->value('value') ?: env('AGORA_APP_ID', '19daeb63b0ec46f2b02197c9fbbe81d6');

        return Inertia::render('Vicon/GuestRoom', [
            'room' => $room,
            'currentParticipant' => $participant,
            'agoraAppId' => $appId,
        ]);
    }

    /**
     * Menerbitkan Token Agora RTC Dinamis untuk Sesi Konferensi
     */
    public function getAgoraToken(Request $request, $uuid)
    {
        $room = ViconRoom::where('uuid', $uuid)
            ->orWhere('room_code', $uuid)
            ->firstOrFail();

        $uid = (int) $request->input('uid');

        if (Auth::check()) {
            $user = Auth::user();
            if ($uid === 0) {
                $uid = (int) $user->id;
            }
        } else {
            $token = $request->input('guest_token');
            $participant = ViconParticipant::where('room_id', $room->id)
                ->where('guest_token', $token)
                ->first();

            if ($participant) {
                $uid = (int) $participant->agora_uid;
            }
        }

        if ($uid === 0) {
            $uid = rand(900000, 999999);
        }

        $token = AgoraTokenService::generateToken($room->agora_channel, $uid);
        $appId = Setting::where('key', 'agora_app_id')->value('value') ?: env('AGORA_APP_ID', '19daeb63b0ec46f2b02197c9fbbe81d6');

        return response()->json([
            'success' => true,
            'appId' => $appId,
            'channel' => $room->agora_channel,
            'token' => $token,
            'uid' => $uid,
        ]);
    }

    /**
     * Sinkronisasi Status Rapat, Partisipan Aktif, dan Pesan Obrolan
     */
    public function syncRoomState(Request $request, $uuid)
    {
        $room = ViconRoom::where('uuid', $uuid)
            ->orWhere('room_code', $uuid)
            ->firstOrFail();

        $participants = ViconParticipant::with('personel')
            ->where('room_id', $room->id)
            ->whereIn('status', ['JOINED', 'INVITED'])
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'agora_uid' => (int) $p->agora_uid,
                    'display_name' => $p->display_name,
                    'role' => $p->role,
                    'status' => $p->status,
                    'photo' => $p->personel?->photo_profile,
                ];
            });

        $lastMsgId = (int) $request->query('last_msg_id', 0);
        $messagesQuery = ViconMessage::where('room_id', $room->id)->orderBy('id', 'asc');
        if ($lastMsgId > 0) {
            $messagesQuery->where('id', '>', $lastMsgId);
        }
        $newMessages = $messagesQuery->get();

        return response()->json([
            'status' => $room->status,
            'participants' => $participants,
            'messages' => $newMessages,
        ]);
    }

    /**
     * Mengirim Pesan Teks Obrolan dalam Ruang Konferensi
     */
    public function sendChatMessage(Request $request, $uuid)
    {
        $room = ViconRoom::where('uuid', $uuid)
            ->orWhere('room_code', $uuid)
            ->firstOrFail();

        $request->validate([
            'message' => 'required|string|max:1000',
            'sender_name' => 'nullable|string|max:100',
            'sender_type' => 'nullable|string',
        ]);

        $senderName = $request->input('sender_name');
        $senderType = $request->input('sender_type', 'GUEST');
        $senderId = null;

        if (Auth::check()) {
            $user = Auth::user();
            $senderId = $user->id;
            $senderType = $user->hasRole('admin') ? 'HOST' : 'PERSONEL';
            if (!$senderName) {
                $rank = $user->personel ? Personel::formatLongRank($user->personel->pangkat) : '';
                $senderName = trim("{$rank} " . ($user->personel ? $user->personel->full_name : $user->name));
            }
        }

        $msg = ViconMessage::create([
            'room_id' => $room->id,
            'sender_name' => $senderName ?: 'Peserta Rapat',
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'message' => strip_tags(trim($request->input('message'))),
        ]);

        return response()->json([
            'success' => true,
            'message' => $msg,
        ]);
    }
}
