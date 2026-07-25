<!DOCTYPE html>
<html>
<head>
    <title>Informasi Akun Personel - {{ $personel->full_name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #000; line-height: 1.4; }
        .kop-surat { font-weight: bold; font-size: 12px; margin-bottom: 2px; }
        .title-block { text-align: center; margin-bottom: 25px; }
        .title { font-size: 15px; font-weight: bold; text-decoration: underline; margin-bottom: 5px; }
        .subtitle { font-size: 11px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #000; }
        th { padding: 8px; font-weight: bold; background-color: #f2f2f2; text-align: center; }
        td { padding: 6px 8px; }
        .text-center { text-align: center; }
        .code-cell { font-family: monospace; font-size: 13px; font-weight: bold; text-align: center; }
        .instruction-box { border: 1px solid #999; padding: 10px 14px; margin-top: 20px; font-size: 10px; background-color: #f9f9f9; }
        .instruction-box ol { margin: 5px 0 0 16px; padding: 0; }
        .instruction-box li { margin-bottom: 3px; }
        .signature-block { float: right; margin-top: 40px; width: 300px; font-size: 11px; text-align: center; page-break-inside: avoid; }
        .clearfix::after { content: ''; display: table; clear: both; }
        .info-row { font-size: 10px; color: #555; margin-top: 4px; }
    </style>
</head>
<body>
    {{-- KOP SURAT --}}
    <div style="display: inline-block; border-bottom: 2px solid #000; padding-bottom: 3px; margin-bottom: 20px; text-align: center;">
        <div class="kop-surat">TENTARA NASIONAL INDONESIA</div>
        <div class="kop-surat">KOMPONEN CADANGAN</div>
    </div>

    {{-- JUDUL --}}
    <div class="title-block">
        <div class="title">SURAT INFORMASI KREDENSIAL AKUN PERSONEL</div>
        <div class="subtitle">NOMOR: {{ $nomorSurat }}</div>
        <div class="info-row">Diterbitkan: {{ $generatedAt }}</div>
    </div>

    {{-- TABEL DATA AKUN PERSONEL --}}
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 20%;">NIKC / USERNAME</th>
                <th>NAMA LENGKAP</th>
                <th style="width: 12%;">PANGKAT</th>
                <th style="width: 8%;">MATRA</th>
                <th style="width: 10%;">ANGKATAN</th>
                <th style="width: 20%;">KATA SANDI (PASSWORD)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td class="code-cell">{{ $personel->nikc ?: $personel->nik }}</td>
                <td>{{ $personel->full_name }}</td>
                <td class="text-center">{{ $personel->pangkat ?: '-' }}</td>
                <td class="text-center">{{ $personel->matra }}</td>
                <td class="text-center">{{ $personel->angkatan ?: '-' }}</td>
                <td class="code-cell">{{ $password }}</td>
            </tr>
        </tbody>
    </table>

    {{-- INSTRUKSI --}}
    <div class="instruction-box">
        <strong>PETUNJUK AKSES MASUK PORTAL SISFOPERSKC:</strong>
        <ol>
            <li>Buka portal web resmi di <strong>https://sisfoperskc.my.id/login</strong>.</li>
            <li>Masukkan <strong>NIKC (Username)</strong> dan <strong>Kata Sandi (Password)</strong> yang tertera pada tabel di atas.</li>
            <li>Setelah berhasil masuk, demi alasan keamanan segera lakukan pembaruan Kata Sandi pada menu <strong>Pengaturan Profil</strong>.</li>
            <li><em>Informasi Akun ini bersifat RAHASIA — jangan berikan kredensial login Anda kepada pihak lain selain personel bersangkutan.</em></li>
        </ol>
    </div>

    {{-- TANDA TANGAN DIGITALLY SIGNED VIA QR SISFOPERSKC --}}
    <div class="clearfix">
        <div class="signature-block">
            <div>Dikeluarkan di: Jakarta</div>
            <div>Pada tanggal: {{ $generatedAt }}</div>
            <div style="margin-top: 10px;">a.n. Komandan Komponen Cadangan</div>
            <div style="font-weight: bold; margin-bottom: 6px;">Administrator SISFOPERSKC,</div>
            
            @if(isset($qrCodeBase64) && $qrCodeBase64)
            <div style="margin: 6px auto; text-align: center;">
                <img src="{{ $qrCodeBase64 }}" style="width: 75px; height: 75px; border: none; padding: 0; background: transparent;" alt="QR Code Signature" />
            </div>
            @else
            <div style="margin-top: 60px;"></div>
            @endif

            <div style="margin-top: 6px; font-weight: bold; text-decoration: underline;">{{ $signerName }}</div>
            <div>{{ $signerPangkat }}</div>
        </div>
    </div>
</body>
</html>
