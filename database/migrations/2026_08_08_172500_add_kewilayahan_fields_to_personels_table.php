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
            if (!Schema::hasColumn('personels', 'kotama')) {
                $table->string('kotama')->nullable();
            }
            if (!Schema::hasColumn('personels', 'satuan_kewilayahan')) {
                $table->string('satuan_kewilayahan')->nullable();
            }
            if (!Schema::hasColumn('personels', 'is_kewilayahan_updated')) {
                $table->boolean('is_kewilayahan_updated')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            if (Schema::hasColumn('personels', 'is_kewilayahan_updated')) {
                $table->dropColumn('is_kewilayahan_updated');
            }
            if (Schema::hasColumn('personels', 'satuan_kewilayahan')) {
                $table->dropColumn('satuan_kewilayahan');
            }
            if (Schema::hasColumn('personels', 'kotama')) {
                $table->dropColumn('kotama');
            }
        });
    }
};
