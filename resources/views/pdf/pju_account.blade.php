<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Informasi Akun PJU - {{ $pju->full_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            background-color: #ffffff;
            font-size: 13px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .logo {
            width: 75px;
            height: auto;
            margin-bottom: 8px;
        }
        .title {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
            margin: 0;
        }
        .subtitle {
            font-size: 12px;
            font-weight: 700;
            color: #2563eb;
            margin: 3px 0 0 0;
            text-transform: uppercase;
        }
        .badge-confidential {
            display: inline-block;
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 4px 12px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            border-radius: 6px;
            margin-top: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .info-table th {
            text-align: left;
            padding: 10px 14px;
            background-color: #f8fafc;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            width: 35%;
        }
        .info-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #0f172a;
        }
        .credentials-box {
            background-color: #eff6ff;
            border: 2px dashed #3b82f6;
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
        }
        .credentials-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1d4ed8;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        .cred-grid {
            display: table;
            width: 100%;
        }
        .cred-row {
            display: table-row;
        }
        .cred-cell-label {
            display: table-cell;
            width: 35%;
            padding: 6px 0;
            font-size: 12px;
            color: #475569;
            font-weight: 600;
        }
        .cred-cell-val {
            display: table-cell;
            padding: 6px 0;
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
            font-family: 'Courier New', Courier, monospace;
        }
        .pass-highlight {
            color: #dc2626;
            background-color: #fef2f2;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #fecaca;
        }
        .security-notice {
            margin-top: 30px;
            padding: 14px;
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
            font-size: 11px;
            color: #92400e;
        }
        .footer {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .footer-left, .footer-right {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }
        .footer-right {
            text-align: center;
        }
        .stamp-box {
            height: 70px;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }
        .btn-print:hover {
            background-color: #1d4ed8;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Lembar Informasi Akun PJU</button>
    </div>

    <div class="header">
        <img src="{{ $settings['logo_tni'] ?? 'https://upload.wikimedia.org/wikipedia/commons/b/b5/Tentara_Nasional_Indonesia_insignia.svg' }}" class="logo" alt="Logo TNI">
        <h1 class="title">LEMBAR INFORMASI AKSES AKUN PEJABAT UTAMA (PJU)</h1>
        <p class="subtitle">SISTEM INFORMASI PERSONEL KOMPONEN CADANGAN (SISFOPERSKC)</p>
        <span class="badge-confidential">RAHASIA & TERBATAS</span>
    </div>

    <table class="info-table">
        <tr>
            <th>Nama Lengkap Pejabat & Pangkat</th>
            <td>{{ $pju->full_name }}</td>
        </tr>
        <tr>
            <th>Jabatan Kedinasan PJU</th>
            <td>{{ $pju->jabatan_pju }}</td>
        </tr>
        <tr>
            <th>Tingkat Otorisasi / Role</th>
            <td style="text-transform: uppercase; color: #2563eb;">{{ str_replace('_', ' ', $pju->role_pju) }}</td>
        </tr>
        <tr>
            <th>Matra Wewenang</th>
            <td>TNI {{ $pju->matra ?? 'SEMUA MATRA (NASIONAL)' }}</td>
        </tr>
        <tr>
            <th>Satuan Wilayah / Komando</th>
            <td>{{ $pju->satuan_wilayah ?? 'Mabes / Pusat Bacadnas' }}</td>
        </tr>
        <tr>
            <th>Alamat Email Dinas</th>
            <td>{{ $pju->email }}</td>
        </tr>
        <tr>
            <th>Nomor WhatsApp Aktif</th>
            <td>{{ $pju->phone_number ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tanggal Pembuatan Akun</th>
            <td>{{ $pju->created_at ? $pju->created_at->translatedFormat('d F Y - H:i') . ' WIB' : date('d F Y - H:i') . ' WIB' }}</td>
        </tr>
    </table>

    <div class="credentials-box">
        <div class="credentials-title">🔑 KREDENSIAL HAK AKSES LOGIN UTAMA</div>
        <div class="cred-grid">
            <div class="cred-row">
                <div class="cred-cell-label">Username Login</div>
                <div class="cred-cell-val">{{ $pju->username }}</div>
            </div>
            <div class="cred-row">
                <div class="cred-cell-label">Alamat Email Login</div>
                <div class="cred-cell-val">{{ $pju->email }}</div>
            </div>
            <div class="cred-row">
                <div class="cred-cell-label">Kata Sandi (Password)</div>
                <div class="cred-cell-val">
                    <span class="pass-highlight">{{ $plainPassword }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="security-notice">
        <strong>PERINGATAN KEAMANAN INFORMASI:</strong><br>
        1. Lembar informasi ini bersifat Rahasia. Harap tidak menyebarluaskan Username dan Kata Sandi ke pihak tidak berwenang.<br>
        2. Pejabat Utama yang bersangkutan disarankan untuk melakukan pengubahan kata sandi secara berkala setelah login pertama kali.
    </div>

    <div class="footer">
        <div class="footer-left">
            <p style="font-size: 10px; color: #94a3b8; margin: 0;">
                Dicetak secara otomatis oleh SISFOPERS System<br>
                ID Dokumen: PJU-DOC-{{ $pju->id }}-{{ date('YmdHis') }}
            </p>
        </div>
        <div class="footer-right">
            <p style="margin: 0; font-size: 11px;">Jakarta, {{ date('d F Y') }}</p>
            <p style="margin: 3px 0 0 0; font-weight: bold; text-transform: uppercase;">Administrator SISFOPERS</p>
            <div class="stamp-box"></div>
            <p style="margin: 0; font-weight: 900; text-decoration: underline;">SUBDIT DIKLAAT / OPERATOR PUSAT</p>
        </div>
    </div>

</body>
</html>
