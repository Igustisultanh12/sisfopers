<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\LiveChatThread;
use App\Models\LiveChatMessage;
use App\Models\Personel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class LiveChatController extends Controller
{
    /**
     * Halaman Utama Live Chat Mandiri untuk Personel Komcad
     */
    public function personelIndex(Request $request)
    {
        $user = Auth::user();
        $personel = $user->personel ?? Personel::where('user_id', $user->id)->first();

        if (!$personel) {
            abort(404, 'Data profil personel tidak ditemukan.');
        }

        $this->touchPersonelPresence($personel->id);

        // Cari utas terbuka aktif atau utas terakhir yang pernah dibuat
        $thread = LiveChatThread::where('personel_id', $personel->id)
            ->where('status', 'OPEN')
            ->latest('last_message_at')
            ->first();

        if (!$thread) {
            $thread = LiveChatThread::where('personel_id', $personel->id)
                ->latest('last_message_at')
                ->first();
        }

        if (!$thread) {
            $thread = LiveChatThread::create([
                'personel_id' => $personel->id,
                'uuid' => (string) Str::uuid(),
                'subject' => 'Pusat Layanan Informasi',
                'status' => 'OPEN',
                'last_message_at' => now(),
                'unread_admin' => 0,
                'unread_personel' => 0,
            ]);
        }

        if ($thread->unread_personel > 0) {
            $thread->update(['unread_personel' => 0]);
        }
        $thread->messages()
            ->where('sender_type', '!=', 'PERSONEL')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $thread->messages()->orderBy('id', 'asc')->get();

        return Inertia::render('Personel/Chat/Index', [
            'initialThread' => $thread,
            'initialMessages' => $messages,
            'personel' => [
                'id' => $personel->id,
                'name' => $personel->full_name,
                'pangkat' => Personel::formatLongRank($personel->pangkat),
                'matra' => $personel->matra,
                'nikc' => $personel->nikc ?? $personel->nik,
                'photo_profile' => $personel->photo_profile,
            ],
        ]);
    }

    /**
     * Memperoleh atau membuka utas obrolan aktif untuk Personel yang sedang masuk
     */
    public function getThread(Request $request)
    {
        $user = Auth::user();
        $personel = $user->personel ?? Personel::where('user_id', $user->id)->first();

        if (!$personel) {
            return response()->json(['error' => 'Profil personel tidak ditemukan.'], 404);
        }

        $this->touchPersonelPresence($personel->id);

        $thread = LiveChatThread::firstOrCreate(
            [
                'personel_id' => $personel->id,
                'status' => 'OPEN',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'subject' => 'Pusat Layanan Informasi',
                'last_message_at' => now(),
                'unread_admin' => 0,
                'unread_personel' => 0,
            ]
        );

        // Reset status pesan yang belum dibaca oleh personel
        if ($thread->unread_personel > 0) {
            $thread->update(['unread_personel' => 0]);
        }
        $thread->messages()
            ->where('sender_type', '!=', 'PERSONEL')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'thread' => $thread,
            'personel' => [
                'id' => $personel->id,
                'name' => $personel->full_name,
                'pangkat' => Personel::formatLongRank($personel->pangkat),
                'matra' => $personel->matra,
                'nikc' => $personel->nikc ?? $personel->nik,
                'photo_profile' => $personel->photo_profile,
            ],
            'messages' => $thread->messages()->orderBy('id', 'asc')->get(),
        ]);
    }

    /**
     * Memeriksa dan mengambil sesi percakapan aktif terkini untuk Personel
     */
    public function getActiveSession(Request $request)
    {
        $user = Auth::user();
        $personel = $user->personel ?? Personel::where('user_id', $user->id)->first();

        if (!$personel) {
            return response()->json(['thread' => null]);
        }

        $this->touchPersonelPresence($personel->id);

        $thread = LiveChatThread::where('personel_id', $personel->id)
            ->where('status', 'OPEN')
            ->latest('last_message_at')
            ->first();

        if (!$thread) {
            return response()->json(['thread' => null]);
        }

        if ($thread->unread_personel > 0) {
            $thread->update(['unread_personel' => 0]);
        }
        $thread->messages()
            ->where('sender_type', '!=', 'PERSONEL')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $callData = Cache::get("live_chat:call:{$thread->uuid}");
        $activeCall = ($callData && in_array($callData['status'], ['RINGING', 'ACCEPTED', 'CONNECTED'])) ? $callData : null;

        return response()->json([
            'thread' => $thread,
            'messages' => $thread->messages()->orderBy('id', 'asc')->get(),
            'call' => $activeCall,
        ]);
    }

    /**
     * Mengambil pembaharuan pesan secara asinkron tanpa penyegaran laman (Personel)
     */
    public function getMessages(Request $request, $uuid)
    {
        $user = Auth::user();
        $personel = $user->personel ?? Personel::where('user_id', $user->id)->first();

        if (!$personel) {
            return response()->json(['error' => 'Profil personel tidak ditemukan.'], 404);
        }

        $this->touchPersonelPresence($personel->id);

        $thread = LiveChatThread::where('uuid', $uuid)
            ->where('personel_id', $personel->id)
            ->firstOrFail();

        if ($thread->unread_personel > 0) {
            $thread->update(['unread_personel' => 0]);
        }
        $thread->messages()
            ->where('sender_type', '!=', 'PERSONEL')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $lastId = (int) $request->query('last_id', 0);
        $messagesQuery = $thread->messages()->orderBy('id', 'asc');

        if ($lastId > 0) {
            $messagesQuery->where('id', '>', $lastId);
        }

        $adminTyping = Cache::get("live_chat:typing:{$thread->uuid}:admin");
        $callData = Cache::get("live_chat:call:{$thread->uuid}");
        $activeCall = ($callData && in_array($callData['status'], ['RINGING', 'ACCEPTED', 'CONNECTED'])) ? $callData : null;

        return response()->json([
            'status' => $thread->status,
            'messages' => $messagesQuery->get(),
            'typing' => [
                'is_typing' => !empty($adminTyping),
                'name' => $adminTyping['name'] ?? null,
            ],
            'call' => $activeCall,
        ]);
    }

    /**
     * Mengirim pesan & berkas bulk unggahan maksimal 15MB dari Personel
     */
    public function sendMessage(Request $request, $uuid)
    {
        $user = Auth::user();
        $personel = $user->personel ?? Personel::where('user_id', $user->id)->first();

        if (!$personel) {
            return response()->json(['error' => 'Profil personel tidak ditemukan.'], 404);
        }

        $this->touchPersonelPresence($personel->id);

        $thread = LiveChatThread::where('uuid', $uuid)
            ->where('personel_id', $personel->id)
            ->firstOrFail();

        if ($thread->status === 'CLOSED') {
            return response()->json(['error' => 'Percakapan ini telah ditutup oleh petugas dinas.'], 403);
        }

        $request->validate([
            'message' => 'nullable|string|max:5000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file',
        ]);

        $files = $request->file('attachments', []);
        $uploadedFiles = [];

        if (!empty($files)) {
            $totalBytes = 0;
            foreach ($files as $file) {
                $totalBytes += $file->getSize();
            }

            // Validasi batas akumulasi unggahan bulk maksimal 15MB
            if ($totalBytes > 15 * 1024 * 1024) {
                return response()->json([
                    'error' => 'Total ukuran berkas lampiran melebihi batas maksimal 15MB.'
                ], 422);
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
            $allowedMimes = [
                'image/jpeg', 'image/png', 'image/webp',
                'application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
            ];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (!in_array($ext, $allowedExtensions)) {
                    return response()->json([
                        'error' => "Ekstensi berkas [{$file->getClientOriginalName()}] tidak diizinkan. Gunakan foto atau dokumen resmi."
                    ], 422);
                }

                $realMime = $finfo->file($file->getRealPath());
                if (!in_array($realMime, $allowedMimes)) {
                    return response()->json([
                        'error' => "Format berkas [{$file->getClientOriginalName()}] tidak sah atau memiliki risiko keamanan MIME."
                    ], 422);
                }

                $cleanOriginalName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $file->getClientOriginalName());
                $cleanOriginalName = preg_replace('/\.+/', '.', $cleanOriginalName);

                $storedPath = $file->store("chat_attachments/{$thread->uuid}", 'private');
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);

                $uploadedFiles[] = [
                    'original_name' => $cleanOriginalName,
                    'file_path' => $storedPath,
                    'mime_type' => $realMime,
                    'size' => $file->getSize(),
                    'is_image' => $isImage,
                ];
            }
        }

        $messageContent = strip_tags(trim((string) $request->input('message')));
        if ($messageContent === '' && empty($uploadedFiles)) {
            return response()->json(['error' => 'Pesan teks atau berkas lampiran wajib diisi.'], 422);
        }

        $senderRank = Personel::formatLongRank($personel->pangkat);
        $senderName = "{$senderRank} {$personel->full_name}";

        $message = LiveChatMessage::create([
            'thread_id' => $thread->id,
            'sender_type' => 'PERSONEL',
            'sender_id' => $personel->id,
            'sender_name' => $senderName,
            'message' => $messageContent !== '' ? $messageContent : null,
            'attachments' => !empty($uploadedFiles) ? $uploadedFiles : null,
            'is_read' => false,
        ]);

        $thread->update([
            'last_message_at' => now(),
            'unread_admin' => $thread->unread_admin + 1,
        ]);

        Cache::forget("live_chat:typing:{$thread->uuid}:personel");

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Memperbarui sinyal indikator sedang mengetik dari Personel
     */
    public function personelTyping(Request $request, $uuid)
    {
        $user = Auth::user();
        $personel = $user->personel ?? Personel::where('user_id', $user->id)->first();

        if (!$personel) {
            return response()->json(['error' => 'Profil personel tidak ditemukan.'], 404);
        }

        $this->touchPersonelPresence($personel->id);

        $thread = LiveChatThread::where('uuid', $uuid)
            ->where('personel_id', $personel->id)
            ->firstOrFail();

        if ($thread->status === 'OPEN') {
            $senderName = trim(Personel::formatLongRank($personel->pangkat) . ' ' . $personel->full_name);
            Cache::put("live_chat:typing:{$thread->uuid}:personel", [
                'name' => $senderName,
            ], now()->addSeconds(4));
        }

        $adminTyping = Cache::get("live_chat:typing:{$thread->uuid}:admin");

        return response()->json([
            'success' => true,
            'typing' => [
                'is_typing' => !empty($adminTyping),
                'name' => $adminTyping['name'] ?? null,
            ],
        ]);
    }

    /**
     * Halaman Dasbor Pusat Obrolan untuk Administrator, PJU, dan Koordinator
     */
    public function adminIndex(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status', 'all');

        $threadsQuery = LiveChatThread::with(['personel.user', 'latestMessage'])
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', strtoupper($status));
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('personel', function ($pq) use ($search) {
                    $pq->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nikc', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_message_at', 'desc');

        $threads = $threadsQuery->paginate(20)->withQueryString();

        $threads->getCollection()->transform(function ($th) {
            if ($th->personel) {
                $th->personel->is_online = Cache::has("live_chat:presence:personel:{$th->personel_id}");
                $th->personel->last_seen_at = Cache::get("live_chat:last_seen:personel:{$th->personel_id}");
            }
            return $th;
        });

        $activeThread = null;
        $activeMessages = [];
        $activeUuid = $request->query('thread');

        if ($activeUuid) {
            $activeThread = LiveChatThread::with('personel.user')
                ->where('uuid', $activeUuid)
                ->first();

            if ($activeThread) {
                if ($activeThread->unread_admin > 0) {
                    $activeThread->update(['unread_admin' => 0]);
                }
                $activeThread->messages()
                    ->where('sender_type', 'PERSONEL')
                    ->where('is_read', false)
                    ->update(['is_read' => true]);

                $activeMessages = $activeThread->messages()->orderBy('id', 'asc')->get();

                if ($activeThread->personel) {
                    $activeThread->personel->is_online = Cache::has("live_chat:presence:personel:{$activeThread->personel_id}");
                    $activeThread->personel->last_seen_at = Cache::get("live_chat:last_seen:personel:{$activeThread->personel_id}");
                }
            }
        }

        $totalOpen = LiveChatThread::where('status', 'OPEN')->count();
        $totalUnread = LiveChatThread::where('unread_admin', '>', 0)->count();

        return Inertia::render('Admin/Chat/Index', [
            'threads' => $threads,
            'activeThread' => $activeThread,
            'activeMessages' => $activeMessages,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'thread' => $activeUuid,
            ],
            'stats' => [
                'total_open' => $totalOpen,
                'total_unread' => $totalUnread,
            ],
        ]);
    }

    /**
     * Sinkronisasi berkala terpadu untuk Admin/PJU/Koordinator:
     * Mengambil daftar utas terbaru, pesan masuk pada utas aktif, status presensi,
     * indikator sedang mengetik, dan statistik obrolan tanpa muat ulang laman.
     */
    public function adminSync(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status', 'all');
        $activeUuid = $request->query('thread');
        $lastId = (int) $request->query('last_id', 0);

        // 1. Ambil 25 utas teratas sesuai kriteria filter
        $threadsQuery = LiveChatThread::with(['personel.user', 'latestMessage'])
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', strtoupper($status));
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('personel', function ($pq) use ($search) {
                    $pq->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nikc', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_message_at', 'desc');

        $threads = $threadsQuery->limit(25)->get();

        $threads->transform(function ($th) {
            if ($th->personel) {
                $th->personel->is_online = Cache::has("live_chat:presence:personel:{$th->personel_id}");
                $th->personel->last_seen_at = Cache::get("live_chat:last_seen:personel:{$th->personel_id}");
            }
            return $th;
        });

        // 2. Pembaharuan data utas aktif jika sedang dibuka di antarmuka
        $activeData = null;
        if ($activeUuid) {
            $activeThread = LiveChatThread::where('uuid', $activeUuid)->first();
            if ($activeThread) {
                if ($activeThread->unread_admin > 0) {
                    $activeThread->update(['unread_admin' => 0]);
                }
                $activeThread->messages()
                    ->where('sender_type', 'PERSONEL')
                    ->where('is_read', false)
                    ->update(['is_read' => true]);

                $messagesQuery = $activeThread->messages()->orderBy('id', 'asc');
                if ($lastId > 0) {
                    $messagesQuery->where('id', '>', $lastId);
                }
                $newMessages = $messagesQuery->get();

                $personelTyping = Cache::get("live_chat:typing:{$activeThread->uuid}:personel");
                $isOnline = Cache::has("live_chat:presence:personel:{$activeThread->personel_id}");
                $lastSeenAt = Cache::get("live_chat:last_seen:personel:{$activeThread->personel_id}");

                $callData = Cache::get("live_chat:call:{$activeThread->uuid}");
                $activeCall = ($callData && in_array($callData['status'], ['RINGING', 'ACCEPTED', 'CONNECTED'])) ? $callData : null;

                $activeData = [
                    'uuid' => $activeThread->uuid,
                    'status' => $activeThread->status,
                    'messages' => $newMessages,
                    'typing' => [
                        'is_typing' => !empty($personelTyping),
                        'name' => $personelTyping['name'] ?? null,
                    ],
                    'presence' => [
                        'is_online' => $isOnline,
                        'last_seen_at' => $lastSeenAt,
                    ],
                    'call' => $activeCall,
                ];
            }
        }

        // Periksa apakah ada panggilan masuk dari personel pada seluruh utas aktif
        $incomingCall = null;
        foreach ($threads as $th) {
            $c = Cache::get("live_chat:call:{$th->uuid}");
            if ($c && $c['status'] === 'RINGING' && ($c['caller']['type'] ?? '') === 'PERSONEL') {
                $incomingCall = $c;
                break;
            }
        }

        $totalOpen = LiveChatThread::where('status', 'OPEN')->count();
        $totalUnread = LiveChatThread::where('unread_admin', '>', 0)->count();

        return response()->json([
            'threads' => $threads,
            'active_thread' => $activeData,
            'incoming_call' => $incomingCall,
            'stats' => [
                'total_open' => $totalOpen,
                'total_unread' => $totalUnread,
            ],
        ]);
    }

    /**
     * Mengambil pembaharuan pesan secara asinkron untuk Admin/PJU/Koordinator
     */
    public function adminGetMessages(Request $request, $uuid)
    {
        $thread = LiveChatThread::with('personel.user')->where('uuid', $uuid)->firstOrFail();

        if ($thread->unread_admin > 0) {
            $thread->update(['unread_admin' => 0]);
        }
        $thread->messages()
            ->where('sender_type', 'PERSONEL')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $lastId = (int) $request->query('last_id', 0);
        $messagesQuery = $thread->messages()->orderBy('id', 'asc');

        if ($lastId > 0) {
            $messagesQuery->where('id', '>', $lastId);
        }

        $personelTyping = Cache::get("live_chat:typing:{$thread->uuid}:personel");
        $isOnline = Cache::has("live_chat:presence:personel:{$thread->personel_id}");
        $lastSeenAt = Cache::get("live_chat:last_seen:personel:{$thread->personel_id}");

        return response()->json([
            'status' => $thread->status,
            'messages' => $messagesQuery->get(),
            'typing' => [
                'is_typing' => !empty($personelTyping),
                'name' => $personelTyping['name'] ?? null,
            ],
            'presence' => [
                'is_online' => $isOnline,
                'last_seen_at' => $lastSeenAt,
            ],
        ]);
    }

    /**
     * Mengirim pesan balasan & lampiran bulk maksimal 15MB dari Admin/PJU/Koordinator
     */
    public function adminSendMessage(Request $request, $uuid)
    {
        $user = Auth::user();
        $thread = LiveChatThread::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'message' => 'nullable|string|max:5000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file',
        ]);

        $files = $request->file('attachments', []);
        $uploadedFiles = [];

        if (!empty($files)) {
            $totalBytes = 0;
            foreach ($files as $file) {
                $totalBytes += $file->getSize();
            }

            if ($totalBytes > 15 * 1024 * 1024) {
                return response()->json([
                    'error' => 'Total ukuran berkas lampiran melebihi batas maksimal 15MB.'
                ], 422);
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
            $allowedMimes = [
                'image/jpeg', 'image/png', 'image/webp',
                'application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
            ];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (!in_array($ext, $allowedExtensions)) {
                    return response()->json([
                        'error' => "Ekstensi berkas [{$file->getClientOriginalName()}] tidak diizinkan."
                    ], 422);
                }

                $realMime = $finfo->file($file->getRealPath());
                if (!in_array($realMime, $allowedMimes)) {
                    return response()->json([
                        'error' => "Format berkas [{$file->getClientOriginalName()}] tidak sah atau memiliki risiko keamanan MIME."
                    ], 422);
                }

                $cleanOriginalName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $file->getClientOriginalName());
                $cleanOriginalName = preg_replace('/\.+/', '.', $cleanOriginalName);

                $storedPath = $file->store("chat_attachments/{$thread->uuid}", 'private');
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);

                $uploadedFiles[] = [
                    'original_name' => $cleanOriginalName,
                    'file_path' => $storedPath,
                    'mime_type' => $realMime,
                    'size' => $file->getSize(),
                    'is_image' => $isImage,
                ];
            }
        }

        $messageContent = strip_tags(trim((string) $request->input('message')));
        if ($messageContent === '' && empty($uploadedFiles)) {
            return response()->json(['error' => 'Pesan teks atau berkas lampiran wajib diisi.'], 422);
        }

        $personelAdmin = $user->personel;
        $rankAdmin = $personelAdmin ? Personel::formatLongRank($personelAdmin->pangkat) : '';
        $nameAdmin = $personelAdmin ? $personelAdmin->full_name : ($user->name ?? $user->username);

        $roleTitle = match (true) {
            $user->hasRole('admin') => 'Admin Sisfopers',
            $user->hasRole('pju') => 'PJU Mabes TNI',
            $user->hasRole('kordinator_angkatan') => 'Koordinator Angkatan' . ($personelAdmin?->angkatan ? " {$personelAdmin->angkatan}" : ''),
            $user->hasRole('kordinator_matra') => 'Koordinator Matra' . ($personelAdmin?->matra ? " {$personelAdmin->matra}" : ''),
            default => 'Operator Pelayanan',
        };

        $parts = array_filter([$roleTitle, $rankAdmin, $nameAdmin]);
        $senderName = implode(' ', $parts);

        $message = LiveChatMessage::create([
            'thread_id' => $thread->id,
            'sender_type' => 'ADMIN',
            'sender_id' => $user->id,
            'sender_name' => $senderName,
            'message' => $messageContent !== '' ? $messageContent : null,
            'attachments' => !empty($uploadedFiles) ? $uploadedFiles : null,
            'is_read' => false,
        ]);

        $thread->update([
            'last_message_at' => now(),
            'unread_personel' => $thread->unread_personel + 1,
        ]);

        Cache::forget("live_chat:typing:{$thread->uuid}:admin");

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Memperbarui sinyal indikator sedang mengetik dari Admin / PJU / Koordinator
     */
    public function adminTyping(Request $request, $uuid)
    {
        $user = Auth::user();
        $thread = LiveChatThread::where('uuid', $uuid)->firstOrFail();

        if ($thread->status === 'OPEN') {
            $personelAdmin = $user->personel;
            $rankAdmin = $personelAdmin ? Personel::formatLongRank($personelAdmin->pangkat) : '';
            $nameAdmin = $personelAdmin ? $personelAdmin->full_name : ($user->name ?? $user->username);

            $roleTitle = match (true) {
                $user->hasRole('admin') => 'Admin Sisfopers',
                $user->hasRole('pju') => 'PJU Mabes TNI',
                $user->hasRole('kordinator_angkatan') => 'Koordinator Angkatan' . ($personelAdmin?->angkatan ? " {$personelAdmin->angkatan}" : ''),
                $user->hasRole('kordinator_matra') => 'Koordinator Matra' . ($personelAdmin?->matra ? " {$personelAdmin->matra}" : ''),
                default => 'Operator Pelayanan',
            };

            $parts = array_filter([$roleTitle, $rankAdmin, $nameAdmin]);
            $senderName = implode(' ', $parts);

            Cache::put("live_chat:typing:{$thread->uuid}:admin", [
                'name' => $senderName,
            ], now()->addSeconds(4));
        }

        $personelTyping = Cache::get("live_chat:typing:{$thread->uuid}:personel");

        return response()->json([
            'success' => true,
            'typing' => [
                'is_typing' => !empty($personelTyping),
                'name' => $personelTyping['name'] ?? null,
            ],
        ]);
    }

    /**
     * Mengubah status utas obrolan (Buka / Tutup Sesi Percakapan)
     * Jika sesi ditutup (CLOSED), seluruh file lampiran otomatis dihapus permanen dari server.
     */
    public function adminToggleStatus(Request $request, $uuid)
    {
        $thread = LiveChatThread::where('uuid', $uuid)->firstOrFail();
        $newStatus = $thread->status === 'OPEN' ? 'CLOSED' : 'OPEN';

        if ($newStatus === 'CLOSED') {
            $thread->purgeFiles();
        }

        $thread->update(['status' => $newStatus]);

        $message = "Status sesi percakapan berhasil diubah menjadi: {$newStatus}." . ($newStatus === 'CLOSED' ? ' Seluruh berkas lampiran telah otomatis dihapus dari server.' : '');

        // Cegah konflik modal Inertia jika request dikirim melalui router Inertia
        if ($request->header('X-Inertia')) {
            return back()->with('success', $message);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Mengakhiri sesi obrolan dari sisi Personel dan otomatis menghapus seluruh berkas lampiran dari server
     */
    public function endSession(Request $request, $uuid)
    {
        $user = Auth::user();
        $personel = $user->personel ?? Personel::where('user_id', $user->id)->first();

        if (!$personel) {
            if ($request->header('X-Inertia')) {
                return back()->with('error', 'Profil personel tidak ditemukan.');
            }
            return response()->json(['error' => 'Profil personel tidak ditemukan.'], 404);
        }

        $thread = LiveChatThread::where('uuid', $uuid)
            ->where('personel_id', $personel->id)
            ->firstOrFail();

        $thread->purgeFiles();
        $thread->update(['status' => 'CLOSED']);

        if ($request->header('X-Inertia')) {
            return back()->with('success', 'Sesi obrolan berhasil diakhiri dan seluruh berkas lampiran telah otomatis dihapus dari server.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi obrolan berhasil diakhiri dan seluruh berkas lampiran telah otomatis dihapus dari server.',
        ]);
    }

    /**
     * Membuka sesi obrolan konsultasi baru bagi Personel setelah sesi sebelumnya diakhiri
     */
    public function newThread(Request $request)
    {
        $user = Auth::user();
        $personel = $user->personel ?? Personel::where('user_id', $user->id)->first();

        if (!$personel) {
            if ($request->header('X-Inertia')) {
                return back()->with('error', 'Profil personel tidak ditemukan.');
            }
            return response()->json(['error' => 'Profil personel tidak ditemukan.'], 404);
        }

        // Pastikan seluruh sesi terbuka terdahulu ditutup dan berkasnya dibersihkan
        $openThreads = LiveChatThread::where('personel_id', $personel->id)
            ->where('status', 'OPEN')
            ->get();

        foreach ($openThreads as $th) {
            $th->purgeFiles();
            $th->update(['status' => 'CLOSED']);
        }

        $newThread = LiveChatThread::create([
            'personel_id' => $personel->id,
            'uuid' => (string) Str::uuid(),
            'subject' => 'Pusat Layanan Informasi',
            'status' => 'OPEN',
            'last_message_at' => now(),
            'unread_admin' => 0,
            'unread_personel' => 0,
        ]);

        if ($request->header('X-Inertia')) {
            return back()->with('success', 'Chat baru berhasil dimulai.');
        }

        return response()->json([
            'success' => true,
            'thread' => $newThread,
            'messages' => [],
        ]);
    }

    /**
     * Pencarian Personel berdasarkan Nama, NIKC, NIK, atau No HP untuk Inisiasi Chat
     */
    public function adminSearchPersonel(Request $request)
    {
        $q = trim((string) $request->input('query', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $cleanQ = preg_replace('/[^A-Za-z0-9]/', '', $q);

        $personels = Personel::where(function ($sub) use ($q, $cleanQ) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nikc', 'like', "%{$q}%")
                    ->orWhere('nik', 'like', "%{$q}%")
                    ->orWhere('phone_number', 'like', "%{$q}%");

                if (!empty($cleanQ)) {
                    $sub->orWhere('nikc', 'like', "%{$cleanQ}%")
                        ->orWhere('nik', 'like', "%{$cleanQ}%");
                }
            })
            ->with(['user'])
            ->limit(15)
            ->get();

        $personelIds = $personels->pluck('id')->toArray();

        // Cek apakah personel sudah memiliki utas chat yang berstatus OPEN
        $openThreads = LiveChatThread::whereIn('personel_id', $personelIds)
            ->where('status', 'OPEN')
            ->pluck('uuid', 'personel_id')
            ->toArray();

        $results = $personels->map(function ($p) use ($openThreads) {
            return [
                'id' => $p->id,
                'full_name' => $p->full_name,
                'pangkat' => Personel::formatLongRank($p->pangkat),
                'pangkat_raw' => $p->pangkat,
                'matra' => $p->matra ?: 'AD',
                'angkatan' => $p->angkatan,
                'nikc' => $p->nikc ?: $p->nik,
                'phone_number' => $p->phone_number ?: '-',
                'email' => $p->user?->email ?: ($p->email ?: '-'),
                'photo_profile' => $p->photo_profile,
                'has_open_thread' => isset($openThreads[$p->id]),
                'open_thread_uuid' => $openThreads[$p->id] ?? null,
                'is_online' => Cache::has("live_chat:presence:personel:{$p->id}"),
                'last_seen_at' => Cache::get("live_chat:last_seen:personel:{$p->id}"),
            ];
        });

        return response()->json($results);
    }

    /**
     * Memulai sesi chat baru dengan personel dari sisi Pengelola (Admin/PJU/Koordinator)
     * Mengirimkan notifikasi multi-channel ke Aplikasi SISFOPERS KC, Email, dan WhatsApp
     */
    public function adminStartChat(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'personel_id' => 'required|exists:personels,id',
            'message' => 'nullable|string|max:5000',
        ]);

        $personel = Personel::with('user')->findOrFail($request->personel_id);

        // 1. Periksa apakah sudah ada utas berstatus OPEN untuk personel ini
        $thread = LiveChatThread::where('personel_id', $personel->id)
            ->where('status', 'OPEN')
            ->latest('last_message_at')
            ->first();

        $isFirstTime = false;

        if (!$thread) {
            $isFirstTime = !LiveChatThread::where('personel_id', $personel->id)->exists();

            $thread = LiveChatThread::create([
                'personel_id' => $personel->id,
                'uuid' => (string) Str::uuid(),
                'subject' => 'Pusat Layanan Informasi',
                'status' => 'OPEN',
                'last_message_at' => now(),
                'unread_admin' => 0,
                'unread_personel' => 0,
            ]);
        }

        // 2. Tentukan nama dan identitas pengirim dinas
        $personelAdmin = $user->personel;
        $rankAdmin = $personelAdmin ? Personel::formatLongRank($personelAdmin->pangkat) : '';
        $nameAdmin = $personelAdmin ? $personelAdmin->full_name : ($user->name ?? $user->username);

        $roleTitle = match (true) {
            $user->hasRole('admin') => 'Admin Sisfopers',
            $user->hasRole('pju') => 'PJU Mabes TNI',
            $user->hasRole('kordinator_angkatan') => 'Koordinator Angkatan' . ($personelAdmin?->angkatan ? " {$personelAdmin->angkatan}" : ''),
            $user->hasRole('kordinator_matra') => 'Koordinator Matra' . ($personelAdmin?->matra ? " {$personelAdmin->matra}" : ''),
            default => 'Operator Pelayanan',
        };

        $parts = array_filter([$roleTitle, $rankAdmin, $nameAdmin]);
        $senderName = implode(' ', $parts);

        // 3. Jika disertakan pesan pembuka awal, simpan sebagai pesan pertama
        $initialMessage = strip_tags(trim((string) $request->input('message')));
        if ($initialMessage !== '') {
            LiveChatMessage::create([
                'thread_id' => $thread->id,
                'sender_type' => 'ADMIN',
                'sender_id' => $user->id,
                'sender_name' => $senderName,
                'message' => $initialMessage,
                'attachments' => null,
                'is_read' => false,
            ]);

            $thread->update([
                'last_message_at' => now(),
                'unread_personel' => $thread->unread_personel + 1,
            ]);
        }

        // 4. Kirim notifikasi multi-channel (Aplikasi SISFOPERS KC, Email, WhatsApp)
        $notification = new \App\Notifications\LiveChatInitiatedNotification(
            $senderName,
            $initialMessage !== '' ? $initialMessage : null,
            route('personel.chat.index')
        );

        if ($personel->user) {
            try {
                $personel->user->notify($notification);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Gagal mengirimkan notifikasi user chat: " . $e->getMessage());
            }
        } else {
            try {
                $notification->via($personel);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Gagal mengirimkan notifikasi personel langsung: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'is_first_time' => $isFirstTime,
            'message' => 'Sesi obrolan berhasil dibuka dan notifikasi telah dikirim ke Aplikasi SISFOPERS KC, Email, serta WhatsApp personel.',
            'thread' => [
                'id' => $thread->id,
                'uuid' => $thread->uuid,
                'status' => $thread->status,
                'personel' => [
                    'id' => $personel->id,
                    'full_name' => $personel->full_name,
                    'pangkat' => Personel::formatLongRank($personel->pangkat),
                    'matra' => $personel->matra,
                    'nikc' => $personel->nikc ?: $personel->nik,
                    'phone_number' => $personel->phone_number,
                    'photo_profile' => $personel->photo_profile,
                ],
            ],
        ]);
    }

    /**
     * Menghapus sesi percakapan dan seluruh lampiran secara permanen oleh pengelola
     */
    public function adminDestroy(Request $request, $uuid)
    {
        $thread = LiveChatThread::where('uuid', $uuid)->firstOrFail();
        $thread->purgeFiles();
        $thread->messages()->delete();
        $thread->delete();

        return back()->with('success', 'Sesi obrolan dan seluruh berkas lampiran berhasil dihapus permanen.');
    }

    /**
     * Memulai panggilan video dinas baru pada utas obrolan (P2P WebRTC)
     */
    public function initiateCall(Request $request, $uuid)
    {
        $user = Auth::user();
        $thread = LiveChatThread::with('personel.user')->where('uuid', $uuid)->firstOrFail();

        $isPersonel = false;
        if ($user->personel && $user->personel->id === $thread->personel_id) {
            $isPersonel = true;
        }

        if ($isPersonel) {
            $caller = [
                'type' => 'PERSONEL',
                'id' => $user->personel->id,
                'name' => $user->personel->full_name,
                'pangkat' => Personel::formatLongRank($user->personel->pangkat),
                'photo' => $user->personel->photo_profile,
            ];
            $callee = [
                'type' => 'OPERATOR',
                'id' => null,
                'name' => 'Petugas Layanan Informasi',
                'pangkat' => 'Pengelola Dinas',
                'photo' => null,
            ];
        } else {
            $callerName = $user->name ?? 'Petugas Layanan';
            $callerRank = 'Pengelola Dinas';
            $callerPhoto = null;

            if ($user->personel) {
                $callerName = $user->personel->full_name;
                $callerRank = Personel::formatLongRank($user->personel->pangkat);
                $callerPhoto = $user->personel->photo_profile;
            }

            $caller = [
                'type' => 'OPERATOR',
                'id' => $user->id,
                'name' => $callerName,
                'pangkat' => $callerRank,
                'photo' => $callerPhoto,
            ];
            $callee = [
                'type' => 'PERSONEL',
                'id' => $thread->personel?->id,
                'name' => $thread->personel?->full_name ?? 'Personel',
                'pangkat' => Personel::formatLongRank($thread->personel?->pangkat ?? ''),
                'photo' => $thread->personel?->photo_profile,
            ];
        }

        $callData = [
            'call_id' => (string) Str::uuid(),
            'thread_uuid' => $thread->uuid,
            'status' => 'RINGING', // RINGING, ACCEPTED, CONNECTED, REJECTED, ENDED
            'caller' => $caller,
            'callee' => $callee,
            'call_type' => $request->input('call_type', 'video'),
            'offer' => null,
            'answer' => null,
            'candidates_caller' => [],
            'candidates_callee' => [],
            'created_at' => now()->timestamp,
            'started_at' => null,
            'ended_at' => null,
        ];

        Cache::put("live_chat:call:{$thread->uuid}", $callData, now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'call' => $callData,
        ]);
    }

    /**
     * Mengambil status sinyal WebRTC pada sesi panggilan aktif
     */
    public function getCallSignal(Request $request, $uuid)
    {
        $callData = Cache::get("live_chat:call:{$uuid}");

        return response()->json([
            'call' => $callData,
        ]);
    }

    /**
     * Mengirimkan sinyal negosiasi WebRTC (Offer, Answer, ICE Candidates, atau Status Terhubung)
     */
    public function sendCallSignal(Request $request, $uuid)
    {
        $callData = Cache::get("live_chat:call:{$uuid}");

        if (!$callData) {
            return response()->json(['error' => 'Sesi panggilan tidak ditemukan atau telah berakhir.'], 404);
        }

        $action = $request->input('action');
        $payload = $request->input('data');
        $sender = $request->input('sender'); // 'caller' | 'callee'

        if ($action === 'accept') {
            $callData['status'] = 'ACCEPTED';
        } elseif ($action === 'offer') {
            $callData['offer'] = $payload;
        } elseif ($action === 'answer') {
            $callData['answer'] = $payload;
        } elseif ($action === 'candidate') {
            if ($sender === 'caller') {
                $callData['candidates_caller'][] = $payload;
            } else {
                $callData['candidates_callee'][] = $payload;
            }
        } elseif ($action === 'connected') {
            $callData['status'] = 'CONNECTED';
            if (empty($callData['started_at'])) {
                $callData['started_at'] = now()->timestamp;
            }
        }

        Cache::put("live_chat:call:{$uuid}", $callData, now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'call' => $callData,
        ]);
    }

    /**
     * Mengakhiri sesi panggilan video dinas dan mencatat ringkasan ke riwayat percakapan
     */
    public function endCall(Request $request, $uuid)
    {
        $callData = Cache::get("live_chat:call:{$uuid}");
        $reason = $request->input('reason', 'ended'); // 'ended', 'rejected', 'canceled', 'missed'
        $durationSeconds = (int) $request->input('duration_seconds', 0);

        if ($callData) {
            $callData['status'] = ($reason === 'rejected') ? 'REJECTED' : 'ENDED';
            $callData['ended_at'] = now()->timestamp;
            Cache::put("live_chat:call:{$uuid}", $callData, now()->addSeconds(30));
        }

        $thread = LiveChatThread::where('uuid', $uuid)->first();
        if ($thread) {
            if ($durationSeconds > 0) {
                $minutes = floor($durationSeconds / 60);
                $seconds = $durationSeconds % 60;
                $formattedTime = sprintf('%02d:%02d', $minutes, $seconds);
                $logMsg = "Panggilan video dinas selesai. (Durasi: {$formattedTime})";
            } elseif ($reason === 'rejected') {
                $logMsg = "Panggilan video dinas ditolak.";
            } elseif ($reason === 'canceled') {
                $logMsg = "Panggilan video dinas dibatalkan.";
            } else {
                $logMsg = "Panggilan video dinas tidak terjawab.";
            }

            LiveChatMessage::create([
                'thread_id' => $thread->id,
                'sender_type' => 'SYSTEM',
                'sender_name' => 'Sistem Vicon Dinas',
                'message' => $logMsg,
                'is_read' => true,
            ]);

            $thread->update(['last_message_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Panggilan berhasil diakhiri.',
        ]);
    }

    /**
     * Memperbarui stempel waktu presensi online Personel pada Cache
     */
    protected function touchPersonelPresence($personelId)
    {
        Cache::put("live_chat:presence:personel:{$personelId}", now()->timestamp, now()->addSeconds(35));
        Cache::put("live_chat:last_seen:personel:{$personelId}", now()->toIso8601String(), now()->addDays(30));
    }
}
