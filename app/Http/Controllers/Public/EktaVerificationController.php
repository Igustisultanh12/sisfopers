<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Ekta;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EktaVerificationController extends Controller
{
    /**
     * Helper untuk sensor nama demi perlindungan privasi data personel
     * Contoh: "I Gusti Sultan Hajjarul Aswat" -> "I Gus*** S**** Haj***** Asw**"
     */
    private function maskFullName(?string $name): string
    {
        if (!$name) return '-';

        $words = preg_split('/\s+/', trim($name));
        $maskedWords = [];

        foreach ($words as $w) {
            $len = mb_strlen($w);
            if ($len <= 2) {
                $maskedWords[] = $w;
            } elseif ($len <= 4) {
                $maskedWords[] = mb_substr($w, 0, 1) . str_repeat('*', $len - 1);
            } elseif ($len <= 6) {
                $maskedWords[] = mb_substr($w, 0, 2) . str_repeat('*', $len - 2);
            } else {
                $maskedWords[] = mb_substr($w, 0, 3) . str_repeat('*', $len - 3);
            }
        }

        return implode(' ', $maskedWords);
    }

    /**
     * Helper untuk sensor nomor registrasi NIKC / NRP
     * Contoh: "12000018012200216" -> "120000180122*****"
     */
    private function maskIdentifier(?string $id): string
    {
        if (!$id) return '-';
        $len = strlen($id);
        if ($len <= 6) return $id;

        $visibleLen = max(6, (int) round($len * 0.65));
        $maskedLen = $len - $visibleLen;

        return substr($id, 0, $visibleLen) . str_repeat('*', $maskedLen);
    }

    /**
     * Tampilkan halaman portal verifikasi publik keabsahan E-KTA
     */
    public function show($code)
    {
        $ekta = Ekta::where('verify_code', $code)
            ->with(['personel.user'])
            ->first();

        $settings = Setting::whereIn('key', [
            'app_name',
            'logo_tni',
            'ekta_logo_komcad',
            'ekta_signer_name',
            'ekta_signer_rank',
            'ekta_signer_title',
            'login_background',
        ])->pluck('value', 'key')->toArray();

        $signerName = $settings['ekta_signer_name'] ?? 'Sri Yanto, S.T.';
        $signerRank = $settings['ekta_signer_rank'] ?? 'Laksamana Muda TNI';
        $signerTitle = $settings['ekta_signer_title'] ?? 'Direktur Jenderal Potensi Pertahanan';

        if (!$ekta || $ekta->status !== 'TERBIT') {
            return Inertia::render('Public/EktaVerification', [
                'is_valid'    => false,
                'verify_code' => $code,
                'message'     => 'Data Kartu Tanda Anggota Elektronik (E-KTA) tidak ditemukan atau belum disahkan oleh Pejabat Berwenang.',
                'settings'    => $settings,
            ]);
        }

        $personel = $ekta->personel;

        $verificationData = [
            'is_valid'          => true,
            'verify_code'       => $ekta->verify_code,
            'nomor_kta'         => $ekta->nomor_kta,
            'nama_personel'     => $this->maskFullName($personel?->full_name),
            'nikc'              => $this->maskIdentifier($personel?->nikc ?: $personel?->user?->username),
            'pangkat'           => $ekta->pangkat ?: ($personel?->pangkat ?: '-'),
            'jabatan'           => $ekta->jabatan ?: 'Anggota Komcad',
            'kesatuan_matra'    => $ekta->kesatuan_matra ?: 'Matra Darat',
            'berlaku_sampai'    => $ekta->berlaku_sampai ?: 'Selama Menjadi Anggota Komcad',
            'tanggal_terbit'    => $ekta->issued_at ? $ekta->issued_at->translatedFormat('d F Y') : '-',
            'signer_name'       => $signerName,
            'signer_rank'       => $signerRank,
            'signer_title'      => $signerTitle,
            'status'            => 'ASLI & SAH TERDAFTAR RESMI',
        ];

        return Inertia::render('Public/EktaVerification', [
            'is_valid' => true,
            'doc'      => $verificationData,
            'settings' => $settings,
        ]);
    }
}
