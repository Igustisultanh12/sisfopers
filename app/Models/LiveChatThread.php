<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LiveChatThread extends Model
{
    protected $table = 'live_chat_threads';

    protected $fillable = [
        'uuid',
        'personel_id',
        'subject',
        'status',
        'last_message_at',
        'unread_admin',
        'unread_personel',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'unread_admin' => 'integer',
        'unread_personel' => 'integer',
    ];

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'personel_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(LiveChatMessage::class, 'thread_id')->orderBy('id', 'asc');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(LiveChatMessage::class, 'thread_id')->latestOfMany();
    }

    protected static function booted(): void
    {
        static::deleting(function (LiveChatThread $thread) {
            $thread->purgeFiles();
        });
    }

    /**
     * Menghapus seluruh berkas lampiran obrolan dari server storage dan memperbarui metadata pesan
     */
    public function purgeFiles(): void
    {
        $chatDir = "chat_attachments/{$this->uuid}";

        // 1. Hapus direktori lampiran sesi ini pada disk private
        if (\Illuminate\Support\Facades\Storage::disk('private')->exists($chatDir)) {
            \Illuminate\Support\Facades\Storage::disk('private')->deleteDirectory($chatDir);
        }

        // 2. Hapus jika ada pada disk public atau local fallback
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($chatDir)) {
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory($chatDir);
        }

        // 3. Hapus path lokal absolut jika masih ada
        $localDir = storage_path("app/private/{$chatDir}");
        if (is_dir($localDir)) {
            \Illuminate\Support\Facades\File::deleteDirectory($localDir);
        }

        $prodDir = "/www/wwwroot/sisfopers.site/storage/app/private/{$chatDir}";
        if (is_dir($prodDir)) {
            \Illuminate\Support\Facades\File::deleteDirectory($prodDir);
        }

        // 4. Perbarui seluruh metadata lampiran pada pesan menjadi purged
        foreach ($this->messages as $msg) {
            if (!empty($msg->attachments)) {
                $purgedAttachments = array_map(function ($att) {
                    $att['file_path'] = null;
                    $att['purged'] = true;
                    return $att;
                }, $msg->attachments);

                $msg->update(['attachments' => $purgedAttachments]);
            }
        }
    }
}
