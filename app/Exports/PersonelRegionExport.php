<?php

namespace App\Exports;

use App\Models\Personel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PersonelRegionExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected string ;
    protected string ;
    protected string ;
    protected string ;

    public function __construct(string , string , string , string )
    {
        ->signerName = ;
        ->signerPangkat = ;
        ->signerNikc = ;
        ->signerJabatan = ;
    }

    public function collection()
    {
        return Personel::where(function () {
                ->whereDoesntHave('registration')
                      ->orWhereHas('registration', function () {
                          ->where('status_verification', 'APPROVED');
                      });
            })
            ->with(['user'])
            ->get()
            ->sortBy(function () {
                return sprintf('%s-%s-%s', ->province, ->city, ->full_name);
            });
    }

    public function headings(): array
    {
        return ['PROVINSI', 'KOTA / KABUPATEN', 'NIKC', 'NAMA LENGKAP', 'PANGKAT', 'MATRA', 'ABITUREN (ANGKATAN)', 'SUMBER REKRUTMEN', 'NO. HP', 'EMAIL', 'STATUS VERIFIKASI'];
    }

    public function map(): array
    {
        return [
            strtoupper(->province ?: 'LAINNYA'),
            strtoupper(->city ?: 'UNASSIGNED'),
            ->nikc ?? '-',
            ->full_name,
            Personel::formatShortRank(->pangkat),
            ->matra,
            ->angkatan ? 'Angkatan ' . ->angkatan : '-',
            ->sumber_rekrutmen ?? 'Reguler',
            ->phone_number,
            ->user?->email ?? '-',
            ->face_verified ? 'TERVERIFIKASI' : 'MENUNGGU'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet ) {
                 = ->sheet->getDelegate();
                ->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                
                ->insertNewRowBefore(1, 5);
                
                ->mergeCells('A1:C1');
                ->setCellValue('A1', 'TENTARA NASIONAL INDONESIA');
                ->getStyle('A1')->getFont()->setBold(true)->setSize(11);
                
                ->mergeCells('A2:C2');
                ->setCellValue('A2', 'KOMPONEN CADANGAN');
                ->getStyle('A2')->getFont()->setBold(true)->setSize(11);
                
                ->getStyle('A3:C3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                 = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
                 = 'R/002/PERS/' . [date('n')] . '/' . date('Y');

                ->setCellValue('D4', 'LAPORAN REKAPITULASI PERSONEL BERBASIS PROVINSI & KOTA');
                ->getStyle('D4')->getFont()->setBold(true)->setSize(12);
                
                ->setCellValue('D5', 'NOMOR: ' . );
                ->getStyle('D5')->getFont()->setBold(true)->setSize(10);
                
                ->getStyle('A6:K6')->getFont()->setBold(true);
                ->getStyle('A6:K6')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF2F2F2');
                
                foreach (range('A', 'K') as ) {
                    ->getColumnDimension()->setAutoSize(true);
                }
                
                 = ->getHighestRow();
                 =  + 3;
                
                ->setCellValue('I' . , 'Dikeluarkan di: Jakarta');
                ->setCellValue('I' . ( + 1), 'Pada tanggal: ' . date('d F Y'));
                ->setCellValue('I' . ( + 3), 'a.n. Komandan Komponen Cadangan');
                ->setCellValue('I' . ( + 4), ->signerJabatan . ',');
                
                ->setCellValue('I' . ( + 8), ->signerName);
                ->getStyle('I' . ( + 8))->getFont()->setBold(true)->setUnderline(true);
                ->setCellValue('I' . ( + 9), ->signerPangkat . ' NIKC. ' . ->signerNikc);
            }
        ];
    }
}
