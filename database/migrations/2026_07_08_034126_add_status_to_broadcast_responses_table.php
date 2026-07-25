<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('broadcast_responses', function (Blueprint $blueprint) {
            // Menambahkan kolom status dengan tipe ENUM taktis dan nilai default 'HADIR'
            // Letakkan setelah kolom personel_id (atau sesuaikan dengan struktur tabel Anda)
            $blueprint->enum('status', ['HADIR', 'IZIN', 'ABSEN'])->default('HADIR')->after('personel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_responses', function (Blueprint $blueprint) {
            // Menghapus kolom status jika melakukan rollback migrasi
            $blueprint->dropColumn('status');
        });
    }
};