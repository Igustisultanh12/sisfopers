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
        Schema::table('personels', function (Blueprint $table) {
            // Cek jika kolom pangkat belum ada, baru buat
            if (!Schema::hasColumn('personels', 'pangkat')) {
                $table->string('pangkat', 50)->nullable()->after('matra');
            }
            
            // Cek jika kolom nikc belum ada, baru buat
            if (!Schema::hasColumn('personels', 'nikc')) {
                $table->string('nikc', 100)->nullable()->after('pangkat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->dropColumn(['pangkat', 'nikc']);
        });
    }
};