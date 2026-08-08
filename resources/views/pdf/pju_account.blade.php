<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Informasi Akun PJU - {{ $pju->full_name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #000; line-height: 1.4; padding: 20px; }
        .kop-surat { font-weight: bold; font-size: 12px; margin-bottom: 2px; }
        .title-block { text-align: center; margin-bottom: 25px; }
        .title { font-size: 15px; font-weight: bold; text-decoration: underline; margin-bottom: 5px; }
        .subtitle { font-size: 11px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #000; }
        th { padding: 8px; font-weight: bold; background-color: #f2f2f2; text-align: center; font-size: 10px; text-transform: uppercase; }
        td { padding: 6px 8px; font-size: 10.5px; }
        .text-center { text-align: center; }
        .code-cell { font-family: monospace; font-size: 12px; font-weight: bold; text-align: center; }
        .instruction-box { border: 1px solid #999; padding: 10px 14px; margin-top: 20px; font-size: 10px; background-color: #f9f9f9; }
        .instruction-box ol { margin: 5px 0 0 16px; padding: 0; }
        .instruction-box li { margin-bottom: 3px; }
        .signature-block { float: right; margin-top: 40px; width: 300px; font-size: 11px; text-align: center; page-break-inside: avoid; }
        .clearfix::after { content: ''; display: table; clear: both; }
        .info-row { font-size: 10px; color: #555; margin-top: 4px; }
        .btn-print { background-color: #2563eb; color: white; border: none; padding: 8px 18px; font-weight: bold; border-radius: 6px; cursor: pointer; margin-bottom: 15px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center;">
        <button onclick="window.print()" class="btn-print">Cetak Dokumen</button>
    </div>

    {{-- KOP SURAT --}}
    <div style="display: inline-block; border-bottom: 2px solid #000; padding-bottom: 3px; margin-bottom: 20px; text-align: center;">
        <div class="kop-surat">TENTARA NASIONAL INDONESIA</div>
        <div class="kop-surat">KOMPONEN CADANGAN</div>
    </div>

    {{-- JUDUL --}}
    <div class="title-block">
        <div class="title">SURAT INFORMASI KREDENSIAL AKUN PEJABAT UTAMA (PJU)</div>
        <div class="subtitle">NOMOR: PJU/ACC/{{ date('Ymd') }}/{{ sprintf('%04d', $pju->id) }}</div>
        <div class="info-row">Diterbitkan: {{ date('d F Y') }}</div>
    </div>

    {{-- TABEL DATA AKUN PJU --}}
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 20%;">USERNAME / EMAIL</th>
                <th>NAMA PEJABAT & PANGKAT</th>
                <th style="width: 12%;">NRP</th>
                <th style="width: 18%;">JABATAN PJU</th>
                <th style="width: 16%;">ROLE & MATRA</th>
                <th style="width: 18%;">KATA SANDI (PASSWORD)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td class="code-cell">
                    {{ $pju->username }}<br>
                    <span style="font-size: 9px; font-family: sans-serif; font-weight: normal; color: #444;">{{ $pju->email }}</span>
                </td>
                <td><strong>{{ $pju->full_name }}</strong></td>
                <td class="text-center">{{ $pju->nrp ?: '-' }}</td>
                <td>{{ $pju->jabatan_pju }}</td>
                <td class="text-center">
                    <strong>{{ str_replace('_', ' ', strtoupper($pju->role_pju)) }}</strong><br>
                    <span style="font-size: 9px; color: #555;">TNI {{ $pju->matra ?? 'SEMUA MATRA' }}</span>
                </td>
                <td class="code-cell" style="color: #dc2626;">{{ $plainPassword }}</td>
            </tr>
        </tbody>
    </table>

    {{-- DETAIL WILAYAH --}}
    <div style="margin-top: 15px; font-size: 10.5px;">
        <strong>Satuan Kewilayahan / Komando Wewenang:</strong> {{ $pju->satuan_wilayah ?? 'Mabes / Pusat Bacadnas' }}
    </div>

    {{-- INSTRUKSI --}}
    <div class="instruction-box">
        <strong>PETUNJUK AKSES MASUK PORTAL PEJABAT UTAMA (SISFOPERSKC):</strong>
        <ol>
            <li>Buka portal web resmi di <strong>https://sisfoperskc.my.id/login</strong>.</li>
            <li>Masukkan <strong>Username / Email Dinas</strong> dan <strong>Kata Sandi (Password)</strong> yang tertera pada tabel di atas.</li>
            <li>Setelah berhasil masuk, demi alasan keamanan segera lakukan pembaruan Kata Sandi pada menu <strong>Pengaturan Profil PJU</strong>.</li>
            <li><em>Informasi Akun ini bersifat RAHASIA — jangan berikan kredensial login Anda kepada pihak lain selain Pejabat Utama bersangkutan.</em></li>
        </ol>
    </div>

    {{-- TANDA TANGAN DIGITALLY SIGNED --}}
    <div class="clearfix">
        <div class="signature-block">
            <div>Dikeluarkan di: Jakarta</div>
            <div>Pada tanggal: {{ date('d F Y') }}</div>
            <div style="margin-top: 10px;">a.n. Komandan Komponen Cadangan</div>
            <div style="font-weight: bold; margin-bottom: 6px;">Administrator SISFOPERSKC,</div>
            
            <div style="margin-top: 50px;"></div>

            <div style="margin-top: 6px; font-weight: bold; text-decoration: underline;">OPERATOR PUSAT SISFOPERS</div>
            <div>Subdit Diklat / Bacadnas</div>
        </div>
    </div>
</body>
</html>
