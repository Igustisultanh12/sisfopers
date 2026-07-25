<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach ($this->masterProvinsi() as $provinsi) {
            DB::table('master_provinsi')->updateOrInsert(
                ['kode_latsarmil' => $provinsi['kode_latsarmil']],
                array_merge($provinsi, [
                    'nama_wilayah' => $provinsi['nama_wilayah'] ?? $provinsi['nama'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        foreach ($this->masterKepangkatan() as $pangkat) {
            DB::table('master_kepangkatan')->updateOrInsert(
                ['nama' => $pangkat['nama']],
                array_merge($pangkat, [
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    public function down(): void
    {
        DB::table('master_provinsi')
            ->whereIn('kode_latsarmil', array_column($this->masterProvinsi(), 'kode_latsarmil'))
            ->delete();

        DB::table('master_kepangkatan')
            ->whereIn('nama', array_column($this->masterKepangkatan(), 'nama'))
            ->delete();
    }

    private function masterProvinsi(): array
    {
        return [
            ['kode_latsarmil' => '01', 'kode_wilayah' => '11', 'nama' => 'Aceh'],
            ['kode_latsarmil' => '02', 'kode_wilayah' => '12', 'nama' => 'Sumatera Utara'],
            ['kode_latsarmil' => '03', 'kode_wilayah' => '13', 'nama' => 'Sumatera Barat'],
            ['kode_latsarmil' => '04', 'kode_wilayah' => '14', 'nama' => 'Riau'],
            ['kode_latsarmil' => '05', 'kode_wilayah' => '21', 'nama' => 'Kepulauan Riau'],
            ['kode_latsarmil' => '06', 'kode_wilayah' => '15', 'nama' => 'Jambi'],
            ['kode_latsarmil' => '07', 'kode_wilayah' => '17', 'nama' => 'Bengkulu'],
            ['kode_latsarmil' => '08', 'kode_wilayah' => '16', 'nama' => 'Sumatera Selatan'],
            ['kode_latsarmil' => '09', 'kode_wilayah' => '19', 'nama' => 'Kepulauan Bangka Belitung'],
            ['kode_latsarmil' => '10', 'kode_wilayah' => '18', 'nama' => 'Lampung'],
            ['kode_latsarmil' => '11', 'kode_wilayah' => '36', 'nama' => 'Banten'],
            ['kode_latsarmil' => '12', 'kode_wilayah' => '32', 'nama' => 'Jawa Barat'],
            ['kode_latsarmil' => '13', 'kode_wilayah' => '31', 'nama' => 'DKI Jakarta'],
            ['kode_latsarmil' => '14', 'kode_wilayah' => '33', 'nama' => 'Jawa Tengah'],
            ['kode_latsarmil' => '15', 'kode_wilayah' => '34', 'nama' => 'Yogyakarta', 'nama_wilayah' => 'DI Yogyakarta'],
            ['kode_latsarmil' => '16', 'kode_wilayah' => '35', 'nama' => 'Jawa Timur'],
            ['kode_latsarmil' => '17', 'kode_wilayah' => '51', 'nama' => 'Bali'],
            ['kode_latsarmil' => '18', 'kode_wilayah' => '52', 'nama' => 'Nusa Tenggara Barat'],
            ['kode_latsarmil' => '19', 'kode_wilayah' => '53', 'nama' => 'Nusa Tenggara Timur'],
            ['kode_latsarmil' => '20', 'kode_wilayah' => '61', 'nama' => 'Kalimantan Barat'],
            ['kode_latsarmil' => '21', 'kode_wilayah' => '63', 'nama' => 'Kalimantan Selatan'],
            ['kode_latsarmil' => '22', 'kode_wilayah' => '62', 'nama' => 'Kalimantan Tengah'],
            ['kode_latsarmil' => '23', 'kode_wilayah' => '64', 'nama' => 'Kalimantan Timur'],
            ['kode_latsarmil' => '24', 'kode_wilayah' => '65', 'nama' => 'Kalimantan Utara'],
            ['kode_latsarmil' => '25', 'kode_wilayah' => '75', 'nama' => 'Gorontalo'],
            ['kode_latsarmil' => '26', 'kode_wilayah' => '76', 'nama' => 'Sulawesi Barat'],
            ['kode_latsarmil' => '27', 'kode_wilayah' => '73', 'nama' => 'Sulawesi Selatan'],
            ['kode_latsarmil' => '28', 'kode_wilayah' => '72', 'nama' => 'Sulawesi Tengah'],
            ['kode_latsarmil' => '29', 'kode_wilayah' => '74', 'nama' => 'Sulawesi Tenggara'],
            ['kode_latsarmil' => '30', 'kode_wilayah' => '71', 'nama' => 'Sulawesi Utara'],
            ['kode_latsarmil' => '31', 'kode_wilayah' => '81', 'nama' => 'Maluku'],
            ['kode_latsarmil' => '32', 'kode_wilayah' => '82', 'nama' => 'Maluku Utara'],
            ['kode_latsarmil' => '33', 'kode_wilayah' => '91', 'nama' => 'Papua'],
            ['kode_latsarmil' => '34', 'kode_wilayah' => '92', 'nama' => 'Papua Barat'],
        ];
    }

    private function masterKepangkatan(): array
    {
        return [
            ['urutan' => 1, 'nama' => 'Prada', 'kelompok_nikc' => '3', 'klaim_langsung' => true],
            ['urutan' => 2, 'nama' => 'Pratu', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 3, 'nama' => 'Praka', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 4, 'nama' => 'Kopda', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 5, 'nama' => 'Koptu', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 6, 'nama' => 'Kopka', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 7, 'nama' => 'Serda', 'kelompok_nikc' => '2', 'klaim_langsung' => true],
            ['urutan' => 8, 'nama' => 'Sertu', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 9, 'nama' => 'Serka', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 10, 'nama' => 'Serma', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 11, 'nama' => 'Pelda', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 12, 'nama' => 'Peltu', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 13, 'nama' => 'Letda', 'kelompok_nikc' => '1', 'klaim_langsung' => true],
            ['urutan' => 14, 'nama' => 'Lettu', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 15, 'nama' => 'Kapten', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 16, 'nama' => 'Mayor', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 17, 'nama' => 'Letkol', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 18, 'nama' => 'Kolonel', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
        ];
    }
};
