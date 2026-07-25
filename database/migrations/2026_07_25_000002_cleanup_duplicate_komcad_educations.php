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
        // Hapus secara permanen seluruh record DIKBATSIS_KOMCAD dari riwayat_pendidikan
        DB::table('riwayat_pendidikan')
            ->where('jenjang', 'DIKBATSIS_KOMCAD')
            ->orWhere('jenjang', 'LIKE', '%DIKBATSIS%')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
