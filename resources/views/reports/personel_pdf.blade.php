<!DOCTYPE html>
<html>
<head>
    <title>Laporan Master Personel Komponen Cadangan</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #000; line-height: 1.4; }
        .kop-surat { font-weight: bold; font-size: 12px; margin-bottom: 2px; }
        .divider { border-top: 2px solid #000; margin-top: 5px; margin-bottom: 20px; }
        .title-block { text-align: center; margin-bottom: 25px; }
        .title { font-size: 15px; font-weight: bold; text-decoration: underline; margin-bottom: 5px; }
        .subtitle { font-size: 11px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #000; }
        th { padding: 8px; font-weight: bold; background-color: #f2f2f2; text-align: center; }
        td { padding: 6px 8px; }
        .text-center { text-align: center; }
        .signature-block { float: right; margin-top: 40px; width: 300px; font-size: 11px; text-align: center; page-break-inside: avoid; }
    </style>
</head>
<body>
    <div style="display: inline-block; border-bottom: 2px solid #000; padding-bottom: 3px; margin-bottom: 20px; text-align: center;">
        <div class="kop-surat">TENTARA NASIONAL INDONESIA</div>
        <div class="kop-surat">KOMPONEN CADANGAN</div>
    </div>

    <div class="title-block">
        <div class="title">LAPORAN DATA KEKUATAN MASTER PERSONEL</div>
        <div class="subtitle">NOMOR: {{ $nomorSurat }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 15%;">NIK</th>
                <th style="width: 15%;">NIKC</th>
                <th>NAMA LENGKAP</th>
                <th style="width: 10%;">PANGKAT</th>
                <th style="width: 8%;">MATRA</th>
                <th style="width: 10%;">ANGKATAN</th>
                <th style="width: 12%;">NOMOR HP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($personels as $p)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td></td>
                <td class="text-center">{{ $p->nikc ?? '-' }}</td>
                <td>{{ $p->full_name }}</td>
                <td class="text-center">{{ \App\Models\Personel::formatShortRank($p->pangkat) }}</td>
                <td class="text-center">{{ $p->matra }}</td>
                <td class="text-center">{{ $p->angkatan }}</td>
                <td>{{ $p->phone_number }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-block">
        <div>Dikeluarkan di: Surabaya</div>
        <div>Pada tanggal: @tanggalId</div>
        <div style="margin-top: 10px;">{{ $signerHeader }}</div>
        <div style="font-weight: bold; margin-bottom: 6px;">{{ $signerJabatan }},</div>

        @if(isset($qrCodeBase64) && $qrCodeBase64)
        <div style="margin: 6px auto; text-align: center;">
            <img src="{{ $qrCodeBase64 }}" style="width: 75px; height: 75px; border: none; padding: 0; background: transparent;" alt="QR Code Signature" />
        </div>
        @else
        <div style="margin-top: 60px;"></div>
        @endif

        <div style="margin-top: 6px; font-weight: bold; text-decoration: underline;">{{ $signerName }}</div>
        <div>{{ $signerPangkat }} NIKC. {{ $signerNikc }}</div>
    </div>
</body>
</html>