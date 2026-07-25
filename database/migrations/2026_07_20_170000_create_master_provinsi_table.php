<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_provinsi')) {
            Schema::table('master_provinsi', function (Blueprint $table) {
                if (!Schema::hasColumn('master_provinsi', 'kode_latsarmil')) {
                    $table->char('kode_latsarmil', 2)->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('master_provinsi', 'kode_wilayah')) {
                    $table->char('kode_wilayah', 2)->nullable()->unique()->after('kode_latsarmil');
                }
                if (!Schema::hasColumn('master_provinsi', 'nama')) {
                    $table->string('nama', 100)->nullable()->unique()->after('kode_wilayah');
                }
                if (!Schema::hasColumn('master_provinsi', 'nama_wilayah')) {
                    $table->string('nama_wilayah', 100)->nullable()->after('nama');
                }
                if (!Schema::hasColumn('master_provinsi', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('nama_wilayah');
                }
            });

            return;
        }

        Schema::create('master_provinsi', function (Blueprint $table) {
            $table->id();
            $table->char('kode_latsarmil', 2)->unique();
            $table->char('kode_wilayah', 2)->nullable()->unique();
            $table->string('nama', 100)->unique();
            $table->string('nama_wilayah', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_provinsi');
    }
};
