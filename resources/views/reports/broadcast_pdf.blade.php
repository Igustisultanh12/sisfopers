<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kehadiran Personel Komponen Cadangan</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #000; line-height: 1.4; }
        .kop-surat { font-weight: bold; font-size: 12px; margin-bottom: 2px; }
        .divider { border-top: 2px solid #000; margin-top: 5px; margin-bottom: 20px; }
        .title-block { text-align: center; margin-bottom: 25px; }
        .title { font-size: 15px; font-weight: bold; text-decoration: underline; margin-bottom: 5px; }
        .subtitle { font-size: 11px; font-weight: bold; }
        .meta-info { margin-bottom: 15px; font-size: 11px; }
        .meta-info table { border: none; margin-top: 0; }
        .meta-info td { border: none; padding: 2px 4px; }
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
        <div class="title">LAPORAN REKAPITULASI PRESENSI KEHADIRAN ANGGOTA</div>
        <div class="subtitle">NOMOR: {{ $nomorSurat }}</div>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td style="font-weight: bold; width: 120px;">Waktu Pelaksanaan</td>
                <td>: {{ date('d F Y', strtotime($broadcast->event_date)) }} | Pukul {{ $broadcast->event_time }} WIB</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Lokasi Penugasan</td>
                <td>: {{ strtoupper($broadcast->location) }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Kategori Kegiatan</td>
                <td>: {{ strtoupper($broadcast->category) }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th>NAMA</th>
                <th style="width: 10%;">PANGKAT</th>
                <th style="width: 12%;">NIKC</th>
                <th style="width: 15%;">ALAMAT</th>
                <th style="width: 12%;">NOMOR HP</th>
                <th style="width: 10%;">SURAT IZIN</th>
                <th style="width: 12%;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($responses as $key => $res)
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>
                <td>{{ $res->personel->full_name ?? '-' }}</td>
                <td class="text-center">{{ $res->personel->pangkat ?? '-' }}</td>
                <td class="text-center">{{ $res->personel->nikc ?? '-' }}</td>
                <td>{{ $res->personel->address ?? '-' }}</td>
                <td>{{ $res->personel->phone_number ?? '-' }}</td>
                <td class="text-center">{{ $res->permit_letter ?? 'TIDAK' }}</td>
                <td class="text-center" style="font-weight: bold;">
                    {{ $res->status_attendance === 'HADIR' ? 'Hadir' : 'Tidak Hadir' }}
                </td>
            </tr>
            @endforeach
            @if(count($responses) === 0)
            <tr>
                <td colspan="8" class="text-center" style="font-style: italic; padding: 15px;">Belum ada data konfirmasi kehadiran anggota.</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="signature-block">
        <div>Dikeluarkan di: Surabaya</div>
        <div>Pada tanggal: @tanggalId</div>
        <div style="margin-top: 10px;">a.n. Komandan Komponen Cadangan</div>
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
