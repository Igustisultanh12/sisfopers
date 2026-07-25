<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterKepangkatan;
use App\Models\Personel;
use App\Models\SystemSetting;

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
        $settings = SystemSetting::pluck('value', 'key')->all();
        
        $signerName = $settings['app_signer_name'] ?? 'HERMAN SUSILO, S.I.P.';
        $signerPangkat = $settings['app_signer_pangkat'] ?? 'KOLONEL INF';
        $signerNikc = $settings['app_signer_nikc'] ?? '112233445566';
        $signerJabatan = $settings['app_signer_jabatan'] ?? 'KOMANDAN KOMPONEN CADANGAN';

        return [
            'name' => $signerName,
            'pangkat' => $signerPangkat,
            'nikc' => $signerNikc,
            'jabatan' => $signerJabatan
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
            new PersonelExport($signer['name'], $signer['pangkat'], $signer['nikc'], $signer['jabatan']), 
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

        $data['personels'] = Personel::where(function ($query) {
                $query->whereDoesntHave('registration')
                      ->orWhereHas('registration', function ($q) {
                          $q->where('status_verification', 'APPROVED');
                      });
            })
            ->with(['user'])
            ->get()
            ->sortByDesc(function($personel) use ($rankOrder) {
                $parts = explode(' ', trim($personel->pangkat));
                $pangkatLower = strtolower($parts[0] ?? '');
                return $rankOrder[$pangkatLower] ?? 0;
            });
        
        $signer = $this->getSignerData();
        $data['signerName'] = $signer['name'];
        $data['signerPangkat'] = $signer['pangkat'];
        $data['signerNikc'] = $signer['nikc'];
        $data['signerJabatan'] = $signer['jabatan'];

        // Format Roman month
        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $data['nomorSurat'] = 'R/001/PERS/' . $romans[date('n')] . '/' . date('Y');

        $docVerif = \App\Models\DocumentVerification::createRecord(
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

        // Load HTML Raw View tanpa CSS eksternal berat demi compliance DomPDF render engine
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
                $signer['jabatan']
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

        $docVerif = \App\Models\DocumentVerification::createRecord(
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
            'broadcast' => $broadcast,
            'responses' => $responses,
            'signerName' => $signer['name'],
            'signerPangkat' => $signer['pangkat'],
            'signerNikc' => $signer['nikc'],
            'signerJabatan' => $signer['jabatan'],
            'nomorSurat' => $nomorSurat,
            'verifyCode' => $docVerif->verify_code,
            'verifyUrl'  => $verifyUrl,
            'qrCodeBase64' => $qrCodeBase64
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Presensi_' . str_replace(' ', '_', $broadcast->title) . '.pdf');
    }

    public function regionPdf()
    {
        $personels = Personel::where(function ($query) {
                $query->whereDoesntHave('registration')
                      ->orWhereHas('registration', function ($q) {
                          $q->where('status_verification', 'APPROVED');
                      });
            })
            ->with(['user', 'sinyalmen'])
            ->get();

        $totalCount = $personels->count();

        $groupedData = $personels->groupBy(function ($item) {
            return strtoupper(trim($item->province ?: 'LAINNYA / UNASSIGNED'));
        })->map(function ($cityGroup) {
            return $cityGroup->groupBy(function ($item) {
                return strtoupper(trim($item->city ?: 'UNASSIGNED'));
            });
        });

        $signer = $this->getSignerData();
        
        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $nomorSurat = 'R/002/PERS/REGIONAL/' . $romans[date('n')] . '/' . date('Y');

        $docVerif = \App\Models\DocumentVerification::createRecord(
            'PERS_REGION_REPORT',
            'LAPORAN REKAPITULASI KEKUATAN PERSONEL BERBASIS WILAYAH',
            'Seluruh Anggota Komcad (' . $totalCount . ' Personel)',
            'Laporan Rekapitulasi Provinsi & Kota/Kabupaten',
            $signer['name'],
            $signer['pangkat'],
            ['nomor_surat' => $nomorSurat]
        );

        $verifyUrl = route('public.verify-doc', $docVerif->verify_code);
        $qrCodeBase64 = \App\Services\QrCodeService::generateBase64($verifyUrl);

        $pdf = Pdf::loadView('reports.region_pdf', [
            'groupedData'   => $groupedData,
            'totalCount'    => $totalCount,
            'signerName'    => $signer['name'],
            'signerPangkat' => $signer['pangkat'],
            'signerNikc'    => $signer['nikc'],
            'signerJabatan' => $signer['jabatan'],
            'nomorSurat'    => $nomorSurat,
            'verifyCode'    => $docVerif->verify_code,
            'verifyUrl'     => $verifyUrl,
            'qrCodeBase64'  => $qrCodeBase64
        ])->setPaper('a4', 'landscape');

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
                $signer['jabatan']
            ), 
            'Laporan_Rekapitulasi_Wilayah_Personel_KC_' . date('YmdHis') . '.xlsx'
        );
    }
}
