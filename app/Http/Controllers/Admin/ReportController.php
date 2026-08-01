<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterKepangkatan;
use App\Models\Personel;
use App\Models\Setting;
use App\Models\DocumentVerification;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PersonelExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Admin/Report/Index');
    }

    private function getSignerData()
    {
        $user = auth()->user();
        $personel = $user?->personel;
        $settings = Setting::pluck('value', 'key')->all();

        $defaultName    = $settings['app_signer_name'] ?? 'HERMAN SUSILO, S.I.P.';
        $defaultPangkat = $settings['app_signer_pangkat'] ?? 'KOLONEL INF';
        $defaultNikc    = $settings['app_signer_nikc'] ?? '112233445566';
        $defaultJabatan = $settings['app_signer_jabatan'] ?? 'KOMANDAN KOMPONEN CADANGAN';

        if ($user && $user->hasRole('komandan')) {
            $rawPangkat = $personel ? $personel->pangkat : $defaultPangkat;
            return [
                'name'    => $personel ? $personel->full_name : ($user->name ?? $defaultName),
                'pangkat' => Personel::formatLongRank($rawPangkat),
                'nikc'    => $personel ? ($personel->nikc ?? $personel->nik ?? $defaultNikc) : $defaultNikc,
                'jabatan' => 'KOMANDAN KOMPONEN CADANGAN',
                'header'  => 'Komandan Komponen Cadangan,'
            ];
        }

        if ($user && ($user->hasRole('kordinator_matra') || $user->hasRole('kordinator_angkatan'))) {
            $matraOrAngkatan = '';
            if ($user->hasRole('kordinator_matra') && $personel) {
                $matraOrAngkatan = 'MATRA ' . strtoupper($personel->matra ?? '');
            } elseif ($user->hasRole('kordinator_angkatan') && $personel) {
                $matraOrAngkatan = 'ANGKATAN ' . ($personel->angkatan ?? '');
            }

            $rawPangkat = $personel ? $personel->pangkat : 'KOORDINATOR';

            return [
                'name'    => $personel ? $personel->full_name : ($user->name ?? 'KOORDINATOR'),
                'pangkat' => Personel::formatLongRank($rawPangkat),
                'nikc'    => $personel ? ($personel->nikc ?? $personel->nik ?? '-') : '-',
                'jabatan' => 'KOORDINATOR ' . trim($matraOrAngkatan),
                'header'  => 'a.n. Komandan Komponen Cadangan,'
            ];
        }

        if ($user && $user->hasRole('admin')) {
            $rawPangkat = $personel ? $personel->pangkat : $defaultPangkat;
            return [
                'name'    => $personel ? $personel->full_name : ($user->name ?? $defaultName),
                'pangkat' => Personel::formatLongRank($rawPangkat),
                'nikc'    => $personel ? ($personel->nikc ?? $personel->nik ?? $defaultNikc) : $defaultNikc,
                'jabatan' => 'ADMINISTRATOR SISFOPERS',
                'header'  => 'a.n. Komandan Komponen Cadangan,'
            ];
        }

        return [
            'name'    => $defaultName,
            'pangkat' => Personel::formatLongRank($defaultPangkat),
            'nikc'    => $defaultNikc,
            'jabatan' => $defaultJabatan,
            'header'  => 'a.n. Komandan Komponen Cadangan,'
        ];
    }

    private function displayValue($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }
        return (string) $value;
    }

    private function formatRank($pangkat)
    {
        return Personel::formatShortRank($pangkat);
    }

    public function personelExcel()
    {
        $signer = $this->getSignerData();
        return Excel::download(
            new PersonelExport($signer['name'], $signer['pangkat'], $signer['nikc'], $signer['jabatan'], $signer['header']), 
            'Laporan_Instansial_Personel_KC_' . date('YmdHis') . '.xlsx'
        );
    }

    public function personelPdf()
    {
        $rankOrder = MasterKepangkatan::where('is_active', true)
            ->get()
            ->mapWithKeys(function ($item) {
                return [strtolower($item->nama) => $item->urutan];
            })
            ->all();

        $user = auth()->user();
        $query = Personel::where(function ($q) {
            $q->whereDoesntHave('registration')
              ->orWhereHas('registration', function ($sub) {
                  $sub->where('status_verification', 'APPROVED');
              });
        });

        if ($user && $user->hasRole('kordinator_matra') && $user->matra) {
            $query->where('matra', $user->matra);
        } elseif ($user && $user->hasRole('kordinator_angkatan')) {
            if ($user->matra) {
                $query->where('matra', $user->matra);
            }
            if ($user->angkatan) {
                $query->where('angkatan', $user->angkatan);
            }
        }

        $data['personels'] = $query->with(['user'])
            ->get()
            ->sortByDesc(function($personel) use ($rankOrder) {
                $parts = explode(' ', trim($personel->pangkat));
                $pangkatLower = strtolower($parts[0] ?? '');
                return $rankOrder[$pangkatLower] ?? 0;
            });
        
        $signer = $this->getSignerData();
        $data['signerName']    = $signer['name'];
        $data['signerPangkat'] = $signer['pangkat'];
        $data['signerNikc']    = $signer['nikc'];
        $data['signerJabatan'] = $signer['jabatan'];
        $data['signerHeader']  = $signer['header'];

        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $data['nomorSurat'] = 'R/001/PERS/' . $romans[date('n')] . '/' . date('Y');

        $docVerif = DocumentVerification::createRecord(
            'PERS_REPORT',
            'LAPORAN DATA KEKUATAN MASTER PERSONEL',
            'Seluruh Anggota Komcad (' . $data['personels']->count() . ' Personel)',
            'Laporan Rekapitulasi Terpusat',
            $signer['name'],
            $signer['pangkat'],
            ['nomor_surat' => $data['nomorSurat']]
        );

        $verifyUrl = route('public.verify-doc', $docVerif->verify_code);
        $data['verifyCode'] = $docVerif->verify_code;
        $data['verifyUrl']  = $verifyUrl;
        $data['qrCodeBase64'] = \App\Services\QrCodeService::generateBase64($verifyUrl);

        $pdf = Pdf::loadView('reports.personel_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('Laporan_Instansial_Personel_KC.pdf');
    }

    public function broadcastExcel($uuid)
    {
        $broadcast = \App\Models\Broadcast::where('uuid', $uuid)->firstOrFail();
        $signer = $this->getSignerData();
        
        return Excel::download(
            new \App\Exports\BroadcastResponseExport(
                $broadcast->id, 
                $signer['name'], 
                $signer['pangkat'], 
                $signer['nikc'], 
                $signer['jabatan'],
                $signer['header']
            ), 
            'Laporan_Presensi_' . str_replace(' ', '_', $broadcast->title) . '_' . date('YmdHis') . '.xlsx'
        );
    }

    public function broadcastPdf($uuid)
    {
        $broadcast = \App\Models\Broadcast::where('uuid', $uuid)->firstOrFail();
        
        $rankOrder = MasterKepangkatan::where('is_active', true)
            ->pluck('urutan', 'nama')
            ->all();

        $responses = \App\Models\BroadcastResponse::where('broadcast_id', $broadcast->id)
            ->with('personel')
            ->get()
            ->sortBy(function($response) use ($rankOrder) {
                $rank = $response->personel?->pangkat;
                return $rankOrder[$rank] ?? 99;
            });

        $signer = $this->getSignerData();
        
        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $nomorSurat = 'R/' . str_pad($broadcast->id, 3, '0', STR_PAD_LEFT) . '/PERS/' . $romans[date('n')] . '/' . date('Y');

        $docVerif = DocumentVerification::createRecord(
            'BROADCAST_PRESENSI',
            'LAPORAN PRESENSI & MONITORING: ' . strtoupper($broadcast->title),
            'Rekapitulasi Respon Personel (' . $responses->count() . ' Anggota)',
            'Kegiatan ' . $broadcast->title,
            $signer['name'],
            $signer['pangkat'],
            ['nomor_surat' => $nomorSurat]
        );

        $verifyUrl = route('public.verify-doc', $docVerif->verify_code);
        $qrCodeBase64 = \App\Services\QrCodeService::generateBase64($verifyUrl);

        $pdf = Pdf::loadView('reports.broadcast_pdf', [
            'broadcast'     => $broadcast,
            'responses'     => $responses,
            'signerName'    => $signer['name'],
            'signerPangkat' => $signer['pangkat'],
            'signerNikc'    => $signer['nikc'],
            'signerJabatan' => $signer['jabatan'],
            'signerHeader'  => $signer['header'],
            'nomorSurat'    => $nomorSurat,
            'verifyCode'    => $docVerif->verify_code,
            'verifyUrl'     => $verifyUrl,
            'qrCodeBase64'  => $qrCodeBase64
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Presensi_' . str_replace(' ', '_', $broadcast->title) . '.pdf');
    }

    public function regionPdf()
    {
        $rekapData = Personel::select(
                DB::raw("UPPER(COALESCE(NULLIF(province, ''), 'LAINNYA / UNASSIGNED')) as provinsi"),
                DB::raw("UPPER(COALESCE(NULLIF(sumber_rekrutmen, ''), 'REGULER')) as sumber"),
                DB::raw("UPPER(COALESCE(NULLIF(city, ''), 'UNASSIGNED')) as kabupaten_kota"),
                DB::raw("CASE WHEN gender = 'P' THEN 'PEREMPUAN' ELSE 'LAKI-LAKI' END as ket"),
                DB::raw("COUNT(*) as total_jumlah"),
                DB::raw("SUM(CASE WHEN face_verified = 1 THEN 1 ELSE 0 END) as total_nyata")
            )
            ->groupBy('provinsi', 'sumber', 'kabupaten_kota', 'ket')
            ->orderBy('provinsi')
            ->orderBy('sumber')
            ->orderBy('kabupaten_kota')
            ->get()
            ->groupBy('provinsi');

        $signer = $this->getSignerData();
        
        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $nomorSurat = 'R/002/PERS/REGIONAL/' . $romans[date('n')] . '/' . date('Y');

        $totalPersonelCount = Personel::count();

        $docVerif = DocumentVerification::createRecord(
            'PERS_REGION_REPORT',
            'LAPORAN REKAPITULASI KEKUATAN PERSONEL DOMISILI PER PROVINSI',
            'Rekapitulasi Wilayah (' . $totalPersonelCount . ' Personel)',
            'Laporan Rekapitulasi Provinsi & Kota/Kabupaten',
            $signer['name'],
            $signer['pangkat'],
            ['nomor_surat' => $nomorSurat]
        );

        $statusCounts = Personel::select('status_keaktifan', DB::raw('COUNT(*) as total'))
            ->whereIn('status_keaktifan', ['MENINGGAL', 'TNI_AD', 'TNI_AL', 'TNI_AU', 'POLRI'])
            ->groupBy('status_keaktifan')
            ->pluck('total', 'status_keaktifan')
            ->all();

        $keteranganList = [];
        if (!empty($statusCounts['MENINGGAL'])) {
            $keteranganList[] = "{$statusCounts['MENINGGAL']} ORANG MENINGGAL DUNIA";
        }
        if (!empty($statusCounts['TNI_AD'])) {
            $keteranganList[] = "{$statusCounts['TNI_AD']} ORANG MASUK TNI AD";
        }
        if (!empty($statusCounts['TNI_AL'])) {
            $keteranganList[] = "{$statusCounts['TNI_AL']} ORANG MASUK TNI AL";
        }
        if (!empty($statusCounts['TNI_AU'])) {
            $keteranganList[] = "{$statusCounts['TNI_AU']} ORANG MASUK TNI AU";
        }
        if (!empty($statusCounts['POLRI'])) {
            $keteranganList[] = "{$statusCounts['POLRI']} ORANG MASUK POLRI";
        }

        if (empty($keteranganList)) {
            $keteranganList[] = "NIHIL / SELURUH PERSONEL AKTIF";
        }

        $verifyUrl = route('public.verify-doc', $docVerif->verify_code);
        $qrCodeBase64 = \App\Services\QrCodeService::generateBase64($verifyUrl);

        $pdf = Pdf::loadView('reports.region_pdf', [
            'rekapData'      => $rekapData,
            'signerName'     => $signer['name'],
            'signerPangkat'  => $signer['pangkat'],
            'signerNikc'     => $signer['nikc'],
            'signerJabatan'  => $signer['jabatan'],
            'signerHeader'   => $signer['header'],
            'nomorSurat'     => $nomorSurat,
            'keteranganList' => $keteranganList,
            'verifyCode'     => $docVerif->verify_code,
            'verifyUrl'      => $verifyUrl,
            'qrCodeBase64'   => $qrCodeBase64
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Laporan_Rekapitulasi_Wilayah_Personel_KC.pdf');
    }

    public function regionExcel()
    {
        $signer = $this->getSignerData();
        return Excel::download(
            new \App\Exports\PersonelRegionExport(
                $signer['name'], 
                $signer['pangkat'], 
                $signer['nikc'], 
                $signer['jabatan'],
                $signer['header']
            ), 
            'Laporan_Rekapitulasi_Wilayah_Personel_KC_' . date('YmdHis') . '.xlsx'
        );
    }
}
