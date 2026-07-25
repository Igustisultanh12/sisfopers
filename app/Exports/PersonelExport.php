<?php

namespace App\Exports;

use App\Models\Personel;
use App\Models\MasterKepangkatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PersonelExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $signerName;
    protected $signerPangkat;
    protected $signerNikc;
    protected $signerJabatan;

    public function __construct($signerName = null, $signerPangkat = null, $signerNikc = null, $signerJabatan = null)
    {
        $this->signerName = $signerName ?: '-';
        $this->signerPangkat = $signerPangkat ?: '-';
        $this->signerNikc = $signerNikc ?: '-';
        $this->signerJabatan = $signerJabatan ?: '-';
    }

    public function collection()
    {
        $rankOrder = MasterKepangkatan::where('is_active', true)
            ->get()
            ->mapWithKeys(function ($item) {
                return [strtolower($item->nama) => $item->urutan];
            })
            ->all();

        return Personel::where(function ($query) {
                $query->whereDoesntHave('registration')
                      ->orWhereHas('registration', function ($q) {
                          $q->where('status_verification', 'APPROVED');
                      });
            })
            ->with(['user', 'sinyalmen'])
            ->get()
            ->sortByDesc(function($personel) use ($rankOrder) {
                $parts = explode(' ', trim($personel->pangkat));
                $pangkatLower = strtolower($parts[0] ?? '');
                return $rankOrder[$pangkatLower] ?? 0;
            });
    }

    public function headings(): array
    {
        return ['NIK', 'NIKC', 'Nama Lengkap', 'Pangkat', 'Matra', 'Angkatan', 'No. HP', 'Email', 'Tinggi (CM)', 'Gol. Darah', 'Status Verifikasi'];
    }

    public function map($personel): array
    {
        return [
            "",
            $personel->nikc ?? '-',
            $personel->full_name,
            \App\Models\Personel::formatShortRank($personel->pangkat),
            $personel->matra,
            $personel->angkatan,
            $personel->phone_number,
            $personel->user?->email ?? '-',
            $personel->sinyalmen->tinggi_badan ?? '-',
            $personel->sinyalmen->golongan_darah ?? '-',
            $personel->face_verified ? 'TERVERIFIKASI' : 'BELUM'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // 1. Set orientation to landscape
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                
                // 2. Insert 5 empty rows before data
                $sheet->insertNewRowBefore(1, 5);
                
                // 3. Write Kop Surat (TNI Komcad)
                $sheet->mergeCells('A1:C1');
                $sheet->setCellValue('A1', 'TENTARA NASIONAL INDONESIA');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells('A2:C2');
                $sheet->setCellValue('A2', 'KOMPONEN CADANGAN');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                // 4. Border separator for Kop (styled to match only the length of text)
                $sheet->getStyle('A3:C3')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                // Calculate Roman month
                $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
                $nomorSurat = 'R/1/PERS/' . $romans[date('n')] . '/' . date('Y');

                // 5. Title of Report
                $sheet->setCellValue('D4', 'LAPORAN REKAPITULASI DATA KEKUATAN PERSONEL');
                $sheet->getStyle('D4')->getFont()->setBold(true)->setSize(12);
                
                $sheet->setCellValue('D5', 'NOMOR: ' . $nomorSurat);
                $sheet->getStyle('D5')->getFont()->setBold(true)->setSize(10);
                
                // 6. Style data headers (which are now at row 6)
                $sheet->getStyle('A6:K6')->getFont()->setBold(true);
                $sheet->getStyle('A6:K6')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF2F2F2');
                
                // Auto-fit columns
                foreach (range('A', 'K') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                // 7. Write Signature block at bottom-right (fetching user details dynamically)
                $highestRow = $sheet->getHighestRow();
                $sigRow = $highestRow + 3;
                
                $sheet->setCellValue('I' . $sigRow, 'Dikeluarkan di: Surabaya');
                $sheet->setCellValue('I' . ($sigRow + 1), 'Pada tanggal: ' . date('d F Y'));
                $sheet->setCellValue('I' . ($sigRow + 3), 'a.n. Komandan Komponen Cadangan');
                $sheet->setCellValue('I' . ($sigRow + 4), $this->signerJabatan . ',');
                
                $sheet->setCellValue('I' . ($sigRow + 8), $this->signerName);
                $sheet->getStyle('I' . ($sigRow + 8))->getFont()->setBold(true)->setUnderline(true);
                $sheet->setCellValue('I' . ($sigRow + 9), $this->signerPangkat . ' NIKC. ' . $this->signerNikc);
                
                // Align signature block to center
                $sheet->getStyle('I' . $sigRow . ':I' . ($sigRow + 9))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        ];
    }
}
