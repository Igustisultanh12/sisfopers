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
        // 1. Bersihkan akhiran " KC" dari tabel master_kepangkatan
        DB::statement("UPDATE master_kepangkatan SET nama = REPLACE(nama, ' KC', '')");

        // 2. Selaraskan data pangkat di tabel personels agar bersih dari akhiran " KC"
        DB::statement("UPDATE personels SET pangkat = REPLACE(pangkat, ' KC', '')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan akhiran " KC" jika dimigrasi mundur (opsional)
        DB::statement("UPDATE master_kepangkatan SET nama = CONCAT(nama, ' KC')");
        DB::statement("UPDATE personels SET pangkat = CONCAT(pangkat, ' KC')");
    }
};
