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
        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $table->text('file_ijazah_path')->nullable()->change();
            $table->text('file_sertifikat_path')->nullable()->change();
            $table->text('ocr_text_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $table->string('file_ijazah_path', 255)->nullable()->change();
            $table->string('file_sertifikat_path', 255)->nullable()->change();
            $table->string('ocr_text_path', 255)->nullable()->change();
        });
    }
};
