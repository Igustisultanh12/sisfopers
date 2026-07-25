<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rekapitulasi Personel Berdasarkan Wilayah</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #000; line-height: 1.3; }
        .kop-surat { font-weight: bold; font-size: 11px; margin-bottom: 2px; }
        .title-block { text-align: center; margin-bottom: 20px; }
        .title { font-size: 13px; font-weight: bold; text-decoration: underline; margin-bottom: 4px; }
        .subtitle { font-size: 10px; font-weight: bold; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table, table.data-table th, table.data-table td { border: 1px solid #000; }
        table.data-table th { background-color: #a3e635; font-weight: bold; text-align: center; padding: 6px 4px; font-size: 10px; text-transform: uppercase; }
        table.data-table td { padding: 5px 8px; font-size: 10px; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        table.summary-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        table.summary-table, table.summary-table th, table.summary-table td { border: 1px solid #000; }
        table.summary-table td { padding: 8px; text-align: center; font-weight: bold; font-size: 10px; }
        
        .signature-block { float: right; margin-top: 25px; width: 280px; font-size: 10px; text-align: center; page-break-inside: avoid; }
    </style>
</head>
<body>
    <div style="display: inline-block; border-bottom: 2px solid #000; padding-bottom: 3px; margin-bottom: 15px; text-align: center;">
        <div class="kop-surat">TENTARA NASIONAL INDONESIA</div>
        <div class="kop-surat">KOMPONEN CADANGAN</div>
    </div>

    <div class="title-block">
        <div class="title">LAPORAN REKAPITULASI KEKUATAN PERSONEL DOMISILI</div>
        <div class="subtitle">NOMOR: {{ $nomorSurat }}</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 6%;">NO.</th>
                <th rowspan="2" style="width: 18%;">SUMBER</th>
                <th rowspan="2" style="width: 38%;">KABUPATEN/KOTA</th>
                <th colspan="2" style="width: 22%;">JUMLAH</th>
                <th rowspan="2" style="width: 16%;">KET.</th>
            </tr>
            <tr>
                <th style="width: 11%;">JUMLAH</th>
                <th style="width: 11%;">NYATA</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotalJumlah = 0;
                $grandTotalNyata = 0;
            @endphp
            @foreach($rekapData as $index => $row)
                @php 
                    $grandTotalJumlah += $row->total_jumlah;
                    $grandTotalNyata += $row->total_nyata;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}.</td>
                    <td class="text-center font-bold">{{ $row->sumber }}</td>
                    <td>{{ $row->kabupaten_kota }}</td>
                    <td class="text-center">{{ $row->total_jumlah }}</td>
                    <td class="text-center">{{ $row->total_nyata }}</td>
                    <td class="text-center font-bold">{{ $row->ket }}</td>
                </tr>
            @endforeach
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-center">{{ $grandTotalJumlah }}</td>
                <td class="text-center">{{ $grandTotalNyata }}</td>
                <td class="text-center">-</td>
            </tr>
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td rowspan="2" style="width: 20%; background-color: #f8fafc; vertical-align: middle;">JUMLAH</td>
            <td style="width: 40%; background-color: #f8fafc;">KESELURUHAN</td>
            <td style="width: 40%; background-color: #f8fafc;">NYATA</td>
        </tr>
        <tr>
            <td style="font-size: 13px; color: #1e3a8a;">{{ $grandTotalJumlah }} PERSONEL</td>
            <td style="font-size: 13px; color: #15803d;">{{ $grandTotalNyata }} PERSONEL TERVERIFIKASI</td>
        </tr>
    </table>

    <div class="signature-block">
        <div>Dikeluarkan di: Jakarta</div>
        <div>Pada tanggal: {{ date('d F Y') }}</div>
        <div style="margin-top: 8px;">a.n. Komandan Komponen Cadangan</div>
        <div>{{ $signerJabatan }},</div>
        
        <div style="margin-top: 15px; margin-bottom: 10px;">
            <img src="{{ $qrCodeBase64 }}" style="width: 75px; height: 75px;" /><br>
            <span style="font-size: 8px; color: #475569;">Kode Verifikasi Legalitas: {{ $verifyCode }}</span>
        </div>
        
        <div style="font-weight: bold; text-decoration: underline;">{{ $signerName }}</div>
        <div>{{ $signerPangkat }} NIKC. {{ $signerNikc }}</div>
    </div>
</body>
</html>
