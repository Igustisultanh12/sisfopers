<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_kepangkatan')) {
            Schema::table('master_kepangkatan', function (Blueprint $table) {
                if (!Schema::hasColumn('master_kepangkatan', 'nama')) {
                    $table->string('nama', 50)->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('master_kepangkatan', 'urutan')) {
                    $table->unsignedTinyInteger('urutan')->nullable()->unique()->after('nama');
                }
                if (!Schema::hasColumn('master_kepangkatan', 'kelompok_nikc')) {
                    $table->char('kelompok_nikc', 1)->nullable()->after('urutan');
                }
                if (!Schema::hasColumn('master_kepangkatan', 'klaim_langsung')) {
                    $table->boolean('klaim_langsung')->default(false)->after('kelompok_nikc');
                }
                if (!Schema::hasColumn('master_kepangkatan', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('klaim_langsung');
                }
            });

            return;
        }

        Schema::create('master_kepangkatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->unsignedTinyInteger('urutan')->unique();
            $table->char('kelompok_nikc', 1);
            $table->boolean('klaim_langsung')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kepangkatan');
    }
};
