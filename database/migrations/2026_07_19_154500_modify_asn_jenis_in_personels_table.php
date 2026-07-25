<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE personels MODIFY COLUMN asn_jenis VARCHAR(50)");
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE personels MODIFY COLUMN asn_jenis ENUM('CPNS', 'PNS', 'P3K')");
        } catch (\Exception $e) {
            // Abaikan jika ada data yang tidak kompatibel dengan ENUM lama
        }
    }
};
