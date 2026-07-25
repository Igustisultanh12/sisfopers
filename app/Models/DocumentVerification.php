<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentVerification extends Model
{
    protected $fillable = [
        'verify_code',
        'doc_type',
        'doc_title',
        'subject_name',
        'subject_identifier',
        'signer_name',
        'signer_title',
        'printed_at',
        'metadata',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
        'metadata'   => 'array',
    ];

    /**
     * Helper untuk membuat record verifikasi dokumen baru secara otomatis
     */
    public static function createRecord(
        string $docType,
        string $docTitle,
        string $subjectName,
        ?string $subjectIdentifier = null,
        ?string $signerName = null,
        ?string $signerTitle = null,
        ?array $metadata = null
    ): self {
        $verifyCode = 'DOC-' . date('Y') . '-' . strtoupper(Str::random(8));

        return self::create([
            'verify_code'        => $verifyCode,
            'doc_type'           => $docType,
            'doc_title'          => $docTitle,
            'subject_name'       => $subjectName,
            'subject_identifier' => $subjectIdentifier,
            'signer_name'        => $signerName ?: (auth()->user()?->personel?->full_name ?? 'Administrator Utama'),
            'signer_title'       => $signerTitle ?: 'Administrator SISFOPERSKC',
            'printed_at'         => now(),
            'metadata'           => $metadata,
        ]);
    }
}
