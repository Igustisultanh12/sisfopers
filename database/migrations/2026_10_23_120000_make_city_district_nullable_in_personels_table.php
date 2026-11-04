<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Validasi pendaftaran menganggap kab/kota & kecamatan opsional (nullable),
 * namun kolomnya masih NOT NULL sehingga pendaftaran tanpa isian tersebut gagal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->string('city', 50)->nullable()->change();
            $table->string('district', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->string('city', 50)->nullable(false)->change();
            $table->string('district', 50)->nullable(false)->change();
        });
    }
};
