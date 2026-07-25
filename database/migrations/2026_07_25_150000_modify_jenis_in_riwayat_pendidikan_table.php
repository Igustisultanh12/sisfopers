<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE riwayat_pendidikan MODIFY COLUMN jenis VARCHAR(50)");
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE riwayat_pendidikan MODIFY COLUMN jenis ENUM('AKADEMIK', 'DIKLAT', 'MILITER')");
        } catch (\Exception $e) {
            // ignore
        }
    }
};
