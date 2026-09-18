<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\LiveChatThread;
use App\Models\LiveChatMessage;
use App\Models\Personel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class LiveChatController extends Controller
{
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

        $thread = LiveChatThread::firstOrCreate(
            [
                'personel_id' => $personel->id,
                'status' => 'OPEN',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'subject' => 'Pusat Bantuan & Konsultasi',
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
     * Mengambil pembaharuan pesan secara asinkron tanpa penyegaran laman (Personel)
     */
    public function getMessages(Request $request, $uuid)
    {
        $user = Auth::user();
        $personel = $user->personel ?? Personel::where('user_id', $user->id)->first();

        if (!$personel) {
            return response()->json(['error' => 'Profil personel tidak ditemukan.'], 404);
        }

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

        return response()->json([
            'status' => $thread->status,
            'messages' => $messagesQuery->get(),
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

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (!in_array($ext, $allowedExtensions)) {
                    return response()->json([
                        'error' => "Ekstensi berkas [{$file->getClientOriginalName()}] tidak diizinkan. Gunakan foto atau dokumen resmi."
                    ], 422);
                }

                $storedPath = $file->store("chat_attachments/{$thread->uuid}", 'private');
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);

                $uploadedFiles[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'is_image' => $isImage,
                ];
            }
        }

        $messageContent = trim((string) $request->input('message'));
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

        return response()->json([
            'success' => true,
            'message' => $message,
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

        return response()->json([
            'status' => $thread->status,
            'messages' => $messagesQuery->get(),
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

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (!in_array($ext, $allowedExtensions)) {
                    return response()->json([
                        'error' => "Ekstensi berkas [{$file->getClientOriginalName()}] tidak diizinkan."
                    ], 422);
                }

                $storedPath = $file->store("chat_attachments/{$thread->uuid}", 'private');
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);

                $uploadedFiles[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'is_image' => $isImage,
                ];
            }
        }

        $messageContent = trim((string) $request->input('message'));
        if ($messageContent === '' && empty($uploadedFiles)) {
            return response()->json(['error' => 'Pesan teks atau berkas lampiran wajib diisi.'], 422);
        }

        $roleName = match (true) {
            $user->hasRole('admin') => 'Admin Sisfopers',
            $user->hasRole('pju') => 'PJU Mabes TNI',
            $user->hasRole('kordinator_angkatan') => 'Koordinator Angkatan',
            $user->hasRole('kordinator_matra') => 'Koordinator Matra',
            default => 'Operator Pelayanan',
        };

        $senderName = "{$user->name} ({$roleName})";

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

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Mengubah status utas obrolan (Buka / Tutup Sesi Percakapan)
     */
    public function adminToggleStatus(Request $request, $uuid)
    {
        $thread = LiveChatThread::where('uuid', $uuid)->firstOrFail();
        $newStatus = $thread->status === 'OPEN' ? 'CLOSED' : 'OPEN';

        $thread->update(['status' => $newStatus]);

        return back()->with('success', "Status sesi percakapan berhasil diubah menjadi: {$newStatus}.");
    }
}
