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
    protected string $signerName;
    protected string $signerPangkat;
    protected string $signerNikc;
    protected string $signerJabatan;

    public function __construct(string $signerName, string $signerPangkat, string $signerNikc, string $signerJabatan)
    {
        $this->signerName = $signerName;
        $this->signerPangkat = $signerPangkat;
        $this->signerNikc = $signerNikc;
        $this->signerJabatan = $signerJabatan;
    }

    public function collection()
    {
        return Personel::where(function ($query) {
                $query->whereDoesntHave('registration')
                      ->orWhereHas('registration', function ($q) {
                          $q->where('status_verification', 'APPROVED');
                      });
            })
            ->with(['user'])
            ->get()
            ->sortBy(function ($personel) {
                return sprintf('%s-%s-%s', $personel->province, $personel->city, $personel->full_name);
            });
    }

    public function headings(): array
    {
        return ['PROVINSI', 'KOTA / KABUPATEN', 'NIKC', 'NAMA LENGKAP', 'PANGKAT', 'MATRA', 'ABITUREN (ANGKATAN)', 'SUMBER REKRUTMEN', 'NO. HP', 'EMAIL', 'STATUS VERIFIKASI'];
    }

    public function map($personel): array
    {
        return [
            strtoupper($personel->province ?: 'LAINNYA'),
            strtoupper($personel->city ?: 'UNASSIGNED'),
            $personel->nikc ?? '-',
            $personel->full_name,
            Personel::formatShortRank($personel->pangkat),
            $personel->matra,
            $personel->angkatan ? 'Angkatan ' . $personel->angkatan : '-',
            $personel->sumber_rekrutmen ?? 'Reguler',
            $personel->phone_number,
            $personel->user?->email ?? '-',
            $personel->face_verified ? 'TERVERIFIKASI' : 'MENUNGGU'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                
                $sheet->insertNewRowBefore(1, 5);
                
                $sheet->mergeCells('A1:C1');
                $sheet->setCellValue('A1', 'TENTARA NASIONAL INDONESIA');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
                
                $sheet->mergeCells('A2:C2');
                $sheet->setCellValue('A2', 'KOMPONEN CADANGAN');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
                
                $sheet->getStyle('A3:C3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
                $nomorSurat = 'R/002/PERS/' . $romans[date('n')] . '/' . date('Y');

                $sheet->setCellValue('D4', 'LAPORAN REKAPITULASI PERSONEL BERBASIS PROVINSI & KOTA');
                $sheet->getStyle('D4')->getFont()->setBold(true)->setSize(12);
                
                $sheet->setCellValue('D5', 'NOMOR: ' . $nomorSurat);
                $sheet->getStyle('D5')->getFont()->setBold(true)->setSize(10);
                
                $sheet->getStyle('A6:K6')->getFont()->setBold(true);
                $sheet->getStyle('A6:K6')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF2F2F2');
                
                foreach (range('A', 'K') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                $highestRow = $sheet->getHighestRow();
                $sigRow = $highestRow + 3;
                
                $sheet->setCellValue('I' . $sigRow, 'Dikeluarkan di: Jakarta');
                $sheet->setCellValue('I' . ($sigRow + 1), 'Pada tanggal: ' . date('d F Y'));
                $sheet->setCellValue('I' . ($sigRow + 3), 'a.n. Komandan Komponen Cadangan');
                $sheet->setCellValue('I' . ($sigRow + 4), $this->signerJabatan . ',');
                
                $sheet->setCellValue('I' . ($sigRow + 8), $this->signerName);
                $sheet->getStyle('I' . ($sigRow + 8))->getFont()->setBold(true)->setUnderline(true);
                $sheet->setCellValue('I' . ($sigRow + 9), $this->signerPangkat . ' NIKC. ' . $this->signerNikc);
            }
        ];
    }
}
