<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broadcasts', function (Blueprint $table) {
            // Menambahkan kolom matra dengan default 'ALL' agar bisa dibroadcast ke semua matra
            $table->string('matra', 10)->default('ALL')->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->dropColumn('matra');
        });
    }
};