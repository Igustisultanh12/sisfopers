<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DocumentVerification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentVerificationController extends Controller
{
    /**
     * Tampilkan halaman verifikasi publik ketika QR Code di-scan
     */
    public function show($verify_code)
    {
        $doc = DocumentVerification::where('verify_code', $verify_code)->first();

        return Inertia::render('Public/DocumentVerification', [
            'verify_code' => $verify_code,
            'doc'         => $doc ? [
                'verify_code'        => $doc->verify_code,
                'doc_type'           => $doc->doc_type,
                'doc_title'          => $doc->doc_title,
                'nomor_surat'        => $doc->metadata['nomor_surat'] ?? null,
                'subject_name'       => $doc->subject_name,
                'subject_identifier' => $doc->subject_identifier,
                'signer_name'        => $doc->signer_name,
                'signer_title'       => $doc->signer_title,
                'printed_at'         => $doc->printed_at ? $doc->printed_at->translatedFormat('d F Y H:i') . ' WIB' : '-',
                'metadata'           => $doc->metadata,
                'is_valid'           => true
            ] : null
        ]);
    }
}
