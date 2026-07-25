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
        if (!Schema::hasColumn('job_histories', 'jabatan')) {
            Schema::table('job_histories', function (Blueprint $table) {
                $table->string('jabatan')->nullable()->after('nama_perusahaan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('job_histories', 'jabatan')) {
            Schema::table('job_histories', function (Blueprint $table) {
                $table->dropColumn('jabatan');
            });
        }
    }
};
