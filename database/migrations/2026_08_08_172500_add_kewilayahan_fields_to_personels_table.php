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
                $table->string('kotama')->nullable()->after('angkatan');
            }
            if (!Schema::hasColumn('personels', 'satuan_kewilayahan')) {
                $table->string('satuan_kewilayahan')->nullable()->after('kotama');
            }
            if (!Schema::hasColumn('personels', 'is_kewilayahan_updated')) {
                $table->boolean('is_kewilayahan_updated')->default(false)->after('satuan_kewilayahan');
            }
        });

        Schema::table('registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('registrations', 'kotama')) {
                $table->string('kotama')->nullable()->after('angkatan');
            }
            if (!Schema::hasColumn('registrations', 'satuan_kewilayahan')) {
                $table->string('satuan_kewilayahan')->nullable()->after('kotama');
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

        Schema::table('registrations', function (Blueprint $table) {
            if (Schema::hasColumn('registrations', 'satuan_kewilayahan')) {
                $table->dropColumn('satuan_kewilayahan');
            }
            if (Schema::hasColumn('registrations', 'kotama')) {
                $table->dropColumn('kotama');
            }
        });
    }
};
