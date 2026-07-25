<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE riwayat_pendidikan MODIFY COLUMN jenjang VARCHAR(50)");
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE riwayat_pendidikan MODIFY COLUMN jenjang ENUM('SD', 'SMP', 'SMA', 'D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI', 'LAIN')");
        } catch (\Exception $e) {
            // Abaikan jika ada data yang tidak kompatibel dengan ENUM lama
        }
    }
};
