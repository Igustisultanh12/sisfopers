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
         = SystemSetting::pluck('value', 'key')->all();
        
         = ['app_signer_name'] ?? 'HERMAN SUSILO, S.I.P.';
         = ['app_signer_pangkat'] ?? 'KOLONEL INF';
         = ['app_signer_nikc'] ?? '112233445566';
         = ['app_signer_jabatan'] ?? 'KOMANDAN KOMPONEN CADANGAN';

        return [
            'name' => ,
            'pangkat' => ,
            'nikc' => ,
            'jabatan' => 
        ];
    }

    private function displayValue(): string
    {
        if ( === null ||  === '') {
            return '-';
        }
        return (string) ;
    }

    private function formatRank()
    {
        return Personel::formatShortRank();
    }

    public function personelExcel()
    {
         = ->getSignerData();
        return Excel::download(
            new PersonelExport(['name'], ['pangkat'], ['nikc'], ['jabatan']), 
            'Laporan_Instansial_Personel_KC_' . date('YmdHis') . '.xlsx'
        );
    }

    public function personelPdf()
    {
         = MasterKepangkatan::where('is_active', true)
            ->get()
            ->mapWithKeys(function () {
                return [strtolower(->nama) => ->urutan];
            })
            ->all();

        ['personels'] = Personel::where(function () {
                ->whereDoesntHave('registration')
                      ->orWhereHas('registration', function () {
                          ->where('status_verification', 'APPROVED');
                      });
            })
            ->with(['user'])
            ->get()
            ->sortByDesc(function() use () {
                 = explode(' ', trim(->pangkat));
                 = strtolower([0] ?? '');
                return [] ?? 0;
            });
        
         = ->getSignerData();
        ['signerName'] = ['name'];
        ['signerPangkat'] = ['pangkat'];
        ['signerNikc'] = ['nikc'];
        ['signerJabatan'] = ['jabatan'];

        // Format Roman month
         = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        ['nomorSurat'] = 'R/001/PERS/' . [date('n')] . '/' . date('Y');

         = \App\Models\DocumentVerification::createRecord(
            'PERS_REPORT',
            'LAPORAN DATA KEKUATAN MASTER PERSONEL',
            'Seluruh Anggota Komcad (' . ['personels']->count() . ' Personel)',
            'Laporan Rekapitulasi Terpusat',
            ['name'],
            ['pangkat'],
            ['nomor_surat' => ['nomorSurat']]
        );

         = route('public.verify-doc', ->verify_code);
        ['verifyCode'] = ->verify_code;
        ['verifyUrl']  = ;
        ['qrCodeBase64'] = \App\Services\QrCodeService::generateBase64();

        // Load HTML Raw View tanpa CSS eksternal berat demi compliance DomPDF render engine
         = Pdf::loadView('reports.personel_pdf', )->setPaper('a4', 'landscape');
        return ->download('Laporan_Instansial_Personel_KC.pdf');
    }

    public function broadcastExcel()
    {
         = \App\Models\Broadcast::where('uuid', )->firstOrFail();
         = ->getSignerData();
        
        return Excel::download(
            new \App\Exports\BroadcastResponseExport(
                ->id, 
                ['name'], 
                ['pangkat'], 
                ['nikc'], 
                ['jabatan']
            ), 
            'Laporan_Presensi_' . str_replace(' ', '_', ->title) . '_' . date('YmdHis') . '.xlsx'
        );
    }

    public function broadcastPdf()
    {
         = \App\Models\Broadcast::where('uuid', )->firstOrFail();
        
         = MasterKepangkatan::where('is_active', true)
            ->pluck('urutan', 'nama')
            ->all();

         = \App\Models\BroadcastResponse::where('broadcast_id', ->id)
            ->with('personel')
            ->get()
            ->sortBy(function() use () {
                 = ->personel?->pangkat;
                return [] ?? 99;
            });

         = ->getSignerData();
        
         = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
         = 'R/' . str_pad(->id, 3, '0', STR_PAD_LEFT) . '/PERS/' . [date('n')] . '/' . date('Y');

         = \App\Models\DocumentVerification::createRecord(
            'BROADCAST_PRESENSI',
            'LAPORAN PRESENSI & MONITORING: ' . strtoupper(->title),
            'Rekapitulasi Respon Personel (' . ->count() . ' Anggota)',
            'Kegiatan ' . ->title,
            ['name'],
            ['pangkat'],
            ['nomor_surat' => ]
        );

         = route('public.verify-doc', ->verify_code);
         = \App\Services\QrCodeService::generateBase64();

         = Pdf::loadView('reports.broadcast_pdf', [
            'broadcast' => ,
            'responses' => ,
            'signerName' => ['name'],
            'signerPangkat' => ['pangkat'],
            'signerNikc' => ['nikc'],
            'signerJabatan' => ['jabatan'],
            'nomorSurat' => ,
            'verifyCode' => ->verify_code,
            'verifyUrl'  => ,
            'qrCodeBase64' => 
        ])->setPaper('a4', 'landscape');

        return ->download('Laporan_Presensi_' . str_replace(' ', '_', ->title) . '.pdf');
    }

    public function regionPdf()
    {
         = Personel::where(function () {
                ->whereDoesntHave('registration')
                      ->orWhereHas('registration', function () {
                          ->where('status_verification', 'APPROVED');
                      });
            })
            ->with(['user', 'sinyalmen'])
            ->get();

         = ->count();

         = ->groupBy(function () {
            return strtoupper(trim(->province ?: 'LAINNYA / UNASSIGNED'));
        })->map(function () {
            return ->groupBy(function () {
                return strtoupper(trim(->city ?: 'UNASSIGNED'));
            });
        });

         = ->getSignerData();
        
         = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
         = 'R/002/PERS/REGIONAL/' . [date('n')] . '/' . date('Y');

         = \App\Models\DocumentVerification::createRecord(
            'PERS_REGION_REPORT',
            'LAPORAN REKAPITULASI KEKUATAN PERSONEL BERBASIS WILAYAH',
            'Seluruh Anggota Komcad (' .  . ' Personel)',
            'Laporan Rekapitulasi Provinsi & Kota/Kabupaten',
            ['name'],
            ['pangkat'],
            ['nomor_surat' => ]
        );

         = route('public.verify-doc', ->verify_code);
         = \App\Services\QrCodeService::generateBase64();

         = Pdf::loadView('reports.region_pdf', [
            'groupedData'   => ,
            'totalCount'    => ,
            'signerName'    => ['name'],
            'signerPangkat' => ['pangkat'],
            'signerNikc'    => ['nikc'],
            'signerJabatan' => ['jabatan'],
            'nomorSurat'    => ,
            'verifyCode'    => ->verify_code,
            'verifyUrl'     => ,
            'qrCodeBase64'  => 
        ])->setPaper('a4', 'landscape');

        return ->download('Laporan_Rekapitulasi_Wilayah_Personel_KC.pdf');
    }

    public function regionExcel()
    {
         = ->getSignerData();
        return Excel::download(
            new \App\Exports\PersonelRegionExport(
                ['name'], 
                ['pangkat'], 
                ['nikc'], 
                ['jabatan']
            ), 
            'Laporan_Rekapitulasi_Wilayah_Personel_KC_' . date('YmdHis') . '.xlsx'
        );
    }
}
