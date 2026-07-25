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
        Schema::table('skep_data', function (Blueprint $table) {
            $table->string('pangkat')->nullable()->after('nama_lengkap');
        });

        Schema::table('skep_requests', function (Blueprint $table) {
            $table->string('pangkat')->nullable()->after('nama_lengkap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skep_data', function (Blueprint $table) {
            $table->dropColumn('pangkat');
        });

        Schema::table('skep_requests', function (Blueprint $table) {
            $table->dropColumn('pangkat');
        });
    }
};
