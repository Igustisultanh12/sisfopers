<?php

namespace App\Exports;

use App\Models\Personel;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PersonelRegionExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected string $signerName;
    protected string $signerPangkat;
    protected string $signerNikc;
    protected string $signerJabatan;
    protected int $rowIndex = 0;

    public function __construct(string $signerName, string $signerPangkat, string $signerNikc, string $signerJabatan)
    {
        $this->signerName = $signerName;
        $this->signerPangkat = $signerPangkat;
        $this->signerNikc = $signerNikc;
        $this->signerJabatan = $signerJabatan;
    }

    public function collection()
    {
        return Personel::select(
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
            ->get();
    }

    public function headings(): array
    {
        return ['PROVINSI', 'NO.', 'SUMBER', 'KABUPATEN/KOTA', 'JUMLAH', 'NYATA', 'KET.'];
    }

    public function map($row): array
    {
        $this->rowIndex++;
        return [
            $row->provinsi,
            $this->rowIndex . '.',
            $row->sumber,
            $row->kabupaten_kota,
            (int) $row->total_jumlah,
            (int) $row->total_nyata,
            $row->ket
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $sheet->insertNewRowBefore(1, 5);
                
                $sheet->mergeCells('A1:C1');
                $sheet->setCellValue('A1', 'TENTARA NASIONAL INDONESIA');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
                
                $sheet->mergeCells('A2:C2');
                $sheet->setCellValue('A2', 'KOMPONEN CADANGAN');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
                
                $sheet->getStyle('A3:C3')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                
                $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
                $nomorSurat = 'R/002/PERS/' . $romans[date('n')] . '/' . date('Y');

                $sheet->setCellValue('D4', 'LAPORAN REKAPITULASI KEKUATAN PERSONEL DOMISILI PER PROVINSI');
                $sheet->getStyle('D4')->getFont()->setBold(true)->setSize(12);
                
                $sheet->setCellValue('D5', 'NOMOR: ' . $nomorSurat);
                $sheet->getStyle('D5')->getFont()->setBold(true)->setSize(10);
                
                $sheet->getStyle('A6:G6')->getFont()->setBold(true);
                $sheet->getStyle('A6:G6')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFA3E635');
                $sheet->getStyle('A6:G6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $highestRow = $sheet->getHighestRow();
                
                $totalRow = $highestRow + 1;
                $sheet->setCellValue('A' . $totalRow, 'TOTAL KESELURUHAN');
                $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
                $sheet->setCellValue('E' . $totalRow, '=SUM(E7:E' . $highestRow . ')');
                $sheet->setCellValue('F' . $totalRow, '=SUM(F7:F' . $highestRow . ')');
                $sheet->setCellValue('G' . $totalRow, '-');
                $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF1F5F9');
                
                $sheet->getStyle('A6:G' . $totalRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                foreach (range('A', 'G') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                $sigRow = $totalRow + 3;
                $sheet->setCellValue('F' . $sigRow, 'Dikeluarkan di: Jakarta');
                $sheet->setCellValue('F' . ($sigRow + 1), 'Pada tanggal: ' . date('d F Y'));
                $sheet->setCellValue('F' . ($sigRow + 3), 'a.n. Komandan Komponen Cadangan');
                $sheet->setCellValue('F' . ($sigRow + 4), $this->signerJabatan . ',');
                
                $sheet->setCellValue('F' . ($sigRow + 8), $this->signerName);
                $sheet->getStyle('F' . ($sigRow + 8))->getFont()->setBold(true)->setUnderline(true);
                $sheet->setCellValue('F' . ($sigRow + 9), $this->signerPangkat . ' NIKC. ' . $this->signerNikc);
            }
        ];
    }
}
