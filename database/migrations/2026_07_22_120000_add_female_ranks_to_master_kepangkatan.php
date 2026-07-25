<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Kosongkan data lama di master_kepangkatan
        DB::table('master_kepangkatan')->truncate();

        // 2. Masukkan ulang data pangkat lengkap beserta pangkat wanita secara teratur
        $now = now();
        $pangkats = [
            ['urutan' => 1, 'nama' => 'Prada', 'kelompok_nikc' => '3', 'klaim_langsung' => true],
            ['urutan' => 2, 'nama' => 'Pratu', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 3, 'nama' => 'Praka', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 4, 'nama' => 'Kopda', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 5, 'nama' => 'Koptu', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 6, 'nama' => 'Kopka', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 7, 'nama' => 'Serda', 'kelompok_nikc' => '2', 'klaim_langsung' => true],
            ['urutan' => 8, 'nama' => 'Serda (W)', 'kelompok_nikc' => '2', 'klaim_langsung' => true],
            ['urutan' => 9, 'nama' => 'Sertu', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 10, 'nama' => 'Serka', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 11, 'nama' => 'Serma', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 12, 'nama' => 'Pelda', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 13, 'nama' => 'Peltu', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 14, 'nama' => 'Letda', 'kelompok_nikc' => '1', 'klaim_langsung' => true],
            ['urutan' => 15, 'nama' => 'Letda (W)', 'kelompok_nikc' => '1', 'klaim_langsung' => true],
            ['urutan' => 16, 'nama' => 'Lettu', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 17, 'nama' => 'Kapten', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 18, 'nama' => 'Mayor', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 19, 'nama' => 'Letkol', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 20, 'nama' => 'Kolonel', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
        ];

        foreach ($pangkats as $pangkat) {
            DB::table('master_kepangkatan')->insert(
                array_merge($pangkat, [
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('master_kepangkatan')->truncate();
    }
};
