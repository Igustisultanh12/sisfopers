<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('skep_data', 'dob')) {
            Schema::table('skep_data', function (Blueprint $table) {
                $table->date('dob')->nullable()->after('nama_lengkap');
            });
        }

        if (!Schema::hasColumn('skep_requests', 'dob')) {
            Schema::table('skep_requests', function (Blueprint $table) {
                $table->date('dob')->nullable()->after('nama_lengkap');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('skep_requests', 'dob')) {
            Schema::table('skep_requests', function (Blueprint $table) {
                $table->dropColumn('dob');
            });
        }

        if (Schema::hasColumn('skep_data', 'dob')) {
            Schema::table('skep_data', function (Blueprint $table) {
                $table->dropColumn('dob');
            });
        }
    }
};
