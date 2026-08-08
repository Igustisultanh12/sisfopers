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
        Schema::table('pjus', function (Blueprint $table) {
            if (!Schema::hasColumn('pjus', 'nrp')) {
                $table->string('nrp')->nullable()->after('full_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pjus', function (Blueprint $table) {
            if (Schema::hasColumn('pjus', 'nrp')) {
                $table->dropColumn('nrp');
            }
        });
    }
};
