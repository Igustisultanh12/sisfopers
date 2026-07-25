<?php

namespace App\Exports;

use App\Models\BroadcastResponse;
use App\Models\MasterKepangkatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class BroadcastResponseExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $broadcastId;
    protected $signerName;
    protected $signerPangkat;
    protected $signerNikc;
    protected $signerJabatan;

    public function __construct($broadcastId, $signerName = null, $signerPangkat = null, $signerNikc = null, $signerJabatan = null)
    {
        $this->broadcastId = $broadcastId;
        $this->signerName = $signerName ?: '-';
        $this->signerPangkat = $signerPangkat ?: '-';
        $this->signerNikc = $signerNikc ?: '-';
        $this->signerJabatan = $signerJabatan ?: '-';
    }

    public function collection()
    {
        $rankOrder = MasterKepangkatan::where('is_active', true)
            ->pluck('urutan', 'nama')
            ->all();

        return BroadcastResponse::where('broadcast_id', $this->broadcastId)
            ->with('personel')
            ->get()
            ->sortBy(function($response) use ($rankOrder) {
                $rank = $response->personel?->pangkat;
                return $rankOrder[$rank] ?? 99;
            });
    }

    public function headings(): array
    {
        return [
            'Nama', 
            'Pangkat', 
            'NIKC', 
            'Alamat', 
            'Nomor HP', 
            'Surat izin', 
            'Status Kehadiran', 
            'Catatan / Alasan'
        ];
    }

    public function map($response): array
    {
        $personel = $response->personel;
        
        return [
            $personel?->full_name ?? '-',
            $personel?->pangkat ?? '-',
            $personel?->nikc ?? '-',
            $personel?->address ?? '-',
            $personel?->phone_number ?? '-',
            $response->permit_letter ?? 'TIDAK',
            $response->status_attendance === 'HADIR' ? 'Hadir' : 'Tidak Hadir',
            $response->notes ?? '-'
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
                $nomorSurat = 'R/' . $this->broadcastId . '/M/' . $romans[date('n')] . '/' . date('Y');

                // 5. Title of Report
                $sheet->setCellValue('C4', 'LAPORAN REKAPITULASI PRESENSI KEHADIRAN ANGGOTA');
                $sheet->getStyle('C4')->getFont()->setBold(true)->setSize(12);
                
                $sheet->setCellValue('C5', 'NOMOR: ' . $nomorSurat);
                $sheet->getStyle('C5')->getFont()->setBold(true)->setSize(10);
                
                // 6. Style data headers (which are now at row 6)
                $sheet->getStyle('A6:H6')->getFont()->setBold(true);
                $sheet->getStyle('A6:H6')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF2F2F2');
                
                // Auto-fit columns
                foreach (range('A', 'H') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                // 7. Write Signature block at bottom-right (fetching user details dynamically)
                $highestRow = $sheet->getHighestRow();
                $sigRow = $highestRow + 3;
                
                $sheet->setCellValue('F' . $sigRow, 'Dikeluarkan di: Surabaya');
                $sheet->setCellValue('F' . ($sigRow + 1), 'Pada tanggal: ' . date('d F Y'));
                $sheet->setCellValue('F' . ($sigRow + 3), 'a.n. Komandan Komponen Cadangan');
                $sheet->setCellValue('F' . ($sigRow + 4), $this->signerJabatan . ',');
                
                $sheet->setCellValue('F' . ($sigRow + 8), $this->signerName);
                $sheet->getStyle('F' . ($sigRow + 8))->getFont()->setBold(true)->setUnderline(true);
                $sheet->setCellValue('F' . ($sigRow + 9), $this->signerPangkat . ' NIKC. ' . $this->signerNikc);
                
                // Align signature block to center
                $sheet->getStyle('F' . $sigRow . ':F' . ($sigRow + 9))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        ];
    }
}
