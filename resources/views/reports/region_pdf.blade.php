<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rekapitulasi Personel Berbasis Wilayah</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #000; line-height: 1.3; }
        .kop-surat { font-weight: bold; font-size: 11px; margin-bottom: 2px; }
        .title-block { text-align: center; margin-bottom: 20px; }
        .title { font-size: 14px; font-weight: bold; text-decoration: underline; margin-bottom: 4px; }
        .subtitle { font-size: 10px; font-weight: bold; }
        .province-header { background-color: #1e293b; color: #fff; font-size: 11px; font-weight: bold; padding: 6px 10px; margin-top: 15px; border-radius: 4px; }
        .city-header { background-color: #f1f5f9; color: #334155; font-size: 10px; font-weight: bold; padding: 4px 8px; margin-top: 8px; border-left: 4px solid #2563eb; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 12px; }
        table, th, td { border: 1px solid #cbd5e1; }
        th { padding: 6px 4px; font-weight: bold; background-color: #f8fafc; text-align: center; font-size: 9px; }
        td { padding: 5px 6px; font-size: 9px; }
        .text-center { text-align: center; }
        .signature-block { float: right; margin-top: 30px; width: 280px; font-size: 10px; text-align: center; page-break-inside: avoid; }
    </style>
</head>
<body>
    <div style="display: inline-block; border-bottom: 2px solid #000; padding-bottom: 3px; margin-bottom: 15px; text-align: center;">
        <div class="kop-surat">TENTARA NASIONAL INDONESIA</div>
        <div class="kop-surat">KOMPONEN CADANGAN</div>
    </div>

    <div class="title-block">
        <div class="title">LAPORAN REKAPITULASI KEKUATAN PERSONEL BERBASIS WILAYAH</div>
        <div class="subtitle">PENGELOMPOKAN BERDASARKAN PROVINSI DAN KOTA/KABUPATEN DOMISILI</div>
        <div style="font-size: 9px; margin-top: 2px;">NOMOR: {{  }}</div>
    </div>

    @php  = 1; @endphp
    @foreach( as  => )
        <div class="province-header">
            PROVINSI: {{  }}
            <span style="float: right; font-weight: normal; font-size: 9px;">Total Personel: {{ ->flatten(1)->count() }}</span>
        </div>

        @foreach( as  => )
            <div class="city-header">
                📍 KOTA / KABUPATEN: {{  }} ({{ ->count() }} Personel)
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">NO</th>
                        <th style="width: 14%;">NIKC</th>
                        <th style="width: 25%;">NAMA LENGKAP</th>
                        <th style="width: 12%;">PANGKAT / MATRA</th>
                        <th style="width: 12%;">ABITUREN (ANGKATAN)</th>
                        <th style="width: 13%;">SUMBER REKRUTMEN</th>
                        <th style="width: 12%;">NOMOR HP</th>
                        <th style="width: 8%;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach( as )
                    <tr>
                        <td class="text-center">{{ ++ }}</td>
                        <td class="text-center">{{ ->nikc ?? '-' }}</td>
                        <td><strong>{{ ->full_name }}</strong></td>
                        <td class="text-center">{{ \App\Models\Personel::formatShortRank(->pangkat) }} ({{ ->matra }})</td>
                        <td class="text-center">{{ ->angkatan ? 'Angkatan ' . ->angkatan : '-' }}</td>
                        <td class="text-center">{{ ->sumber_rekrutmen ?? 'Reguler' }}</td>
                        <td class="text-center">{{ ->phone_number }}</td>
                        <td class="text-center">{{ ->face_verified ? 'VERIFIED' : 'PENDING' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endforeach

    <div style="margin-top: 20px; font-size: 9px; font-weight: bold;">
        TOTAL KESELURUHAN PERSONEL TERDAPAT PADA DOKUMEN: {{  }} PERSONEL
    </div>

    <!-- Blok Tanda Tangan & QR Code -->
    <div class="signature-block">
        <div>Dikeluarkan di: Jakarta</div>
        <div>Pada tanggal: {{ date('d F Y') }}</div>
        <div style="margin-top: 8px;">a.n. Komandan Komponen Cadangan</div>
        <div>{{  }},</div>
        
        <div style="margin-top: 15px; margin-bottom: 10px;">
            <img src="{{  }}" style="width: 80px; height: 80px;" /><br>
            <span style="font-size: 8px; color: #475569;">Kode Verifikasi Legalitas: {{  }}</span>
        </div>
        
        <div style="font-weight: bold; text-decoration: underline;">{{  }}</div>
        <div>{{  }} NIKC. {{  }}</div>
    </div>
</body>
</html>
