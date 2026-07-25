<!DOCTYPE html>
<html>
<head>
    <title>Laporan OTP Reset Password Massal - Komponen Cadangan</title>
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
        .otp-code { font-size: 13px; font-weight: bold; letter-spacing: 3px; text-align: center; }
        .instruction-box { border: 1px solid #999; padding: 10px 14px; margin-top: 20px; font-size: 10px; background-color: #f9f9f9; }
        .instruction-box ol { margin: 5px 0 0 16px; padding: 0; }
        .instruction-box li { margin-bottom: 3px; }
        .signature-block { float: right; margin-top: 40px; width: 300px; font-size: 11px; text-align: center; page-break-inside: avoid; }
        .clearfix::after { content: ''; display: table; clear: both; }
        .info-row { font-size: 10px; color: #555; margin-top: 4px; }
        .summary-box { font-size: 10px; margin-top: 8px; }
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
        <div class="title">LAPORAN KODE OTP RESET PASSWORD PERSONEL</div>
        <div class="subtitle">NOMOR: {{ $nomorSurat }}</div>
        <div class="info-row">Diterbitkan: {{ $generatedAt }}</div>
    </div>

    {{-- RINGKASAN --}}
    <div class="summary-box">
        Jumlah Personel: <strong>{{ $personels->count() }} orang</strong> &nbsp;|&nbsp;
        Masa berlaku OTP: <strong>24 jam sejak tanggal diterbitkan</strong>
    </div>

    {{-- TABEL KEKUATAN PERSONEL + OTP --}}
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 17%;">NIKC</th>
                <th>NAMA LENGKAP</th>
                <th style="width: 10%;">PANGKAT</th>
                <th style="width: 8%;">MATRA</th>
                <th style="width: 13%;">NOMOR HP</th>
                <th style="width: 11%;">KODE OTP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($personels as $p)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $p->nikc ?? '-' }}</td>
                <td>{{ $p->full_name }}</td>
                <td class="text-center">{{ $p->pangkat ?? '-' }}</td>
                <td class="text-center">{{ $p->matra }}</td>
                <td>{{ $p->phone_number }}</td>
                <td class="otp-code">{{ $p->reset_password_otp }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- INSTRUKSI --}}
    <div class="instruction-box">
        <strong>PETUNJUK RESET PASSWORD MENGGUNAKAN OTP:</strong>
        <ol>
            <li>Buka halaman <strong>sisfoperskc.my.id/reset-password-otp</strong> (tanpa login).</li>
            <li>Masukkan <strong>NIKC</strong> Anda pada kolom NIKC.</li>
            <li>Masukkan 6 digit <strong>Kode OTP Reset Password</strong> sesuai nama Anda pada tabel di atas.</li>
            <li>Masukkan Password Baru Anda dan Konfirmasi Password Baru tersebut.</li>
            <li>Klik tombol <strong>Reset Password</strong>. Kata sandi Anda akan langsung diperbarui.</li>
            <li><em>OTP bersifat RAHASIA — distribusikan hanya kepada personel bersangkutan.</em></li>
        </ol>
    </div>

    {{-- TANDA TANGAN DIGITALLY SIGNED VIA QR SISFOPERSKC --}}
    <div class="clearfix">
        <div class="signature-block">
            <div>Dikeluarkan di: Surabaya</div>
            <div>Pada tanggal: {{ $generatedAt }}</div>
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
    </div>
</body>
</html>
