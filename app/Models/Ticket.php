<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'ticket_number',
        'personel_id',
        'category',
        'description',
        'attachment_path',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->ticket_number)) {
                $model->ticket_number = static::generateTicketNumber($model->category);
            }
        });
    }

    /**
     * Format: TK-PERS-ddmmyy[TYPE]XXX
     * Misal 01 Agustus 2026 -> 010826
     * TYPE: KTA (Cetak KTA), PHT (Ganti Foto), BDT (Ubah Data / Biodata)
     */
    public static function generateTicketNumber(string $category): string
    {
        $dateStr = date('dmy'); // 010826
        
        $typeCode = match ($category) {
            'CETAK_KTA' => 'KTA',
            'UBAH_FOTO' => 'PHT',
            'UBAH_DATA'  => 'BDT',
            default     => 'GEN',
        };

        $prefix = "TK-PERS-{$dateStr}{$typeCode}";

        // Cari nomor urut terakhir pada hari ini untuk tipe ini
        $latest = static::withTrashed()
            ->where('ticket_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastSeqStr = substr($latest->ticket_number, -3);
            $nextSeq = intval($lastSeqStr) + 1;
        } else {
            $nextSeq = 1;
        }

        $seqFormatted = sprintf('%03d', $nextSeq);

        return "{$prefix}{$seqFormatted}";
    }

    public function personel()
    {
        return $this->belongsTo(Personel::class, 'personel_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class, 'ticket_id')->with('user.personel', 'user.role')->orderBy('created_at', 'asc');
    }
}
