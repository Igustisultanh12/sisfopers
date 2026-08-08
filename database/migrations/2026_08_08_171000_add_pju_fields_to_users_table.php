<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'matra')) {
                $table->string('matra', 10)->nullable();
            }
            if (!Schema::hasColumn('users', 'jabatan_pju')) {
                $table->string('jabatan_pju')->nullable();
            }
            if (!Schema::hasColumn('users', 'satuan_wilayah')) {
                $table->string('satuan_wilayah')->nullable();
            }
        });

        // Insert new PJU roles if not exists
        $pjuRoles = [
            'ka_bacadnas',
            'ses_bacadnas',
            'kapus_komcad',
            'pembina_matra',
            'pembina_kodam',
            'pembina_kodaeral',
            'pembina_kodau',
            'pembina_kodim',
            'pembina_lanal',
            'pembina_lanud',
        ];

        foreach ($pjuRoles as $roleName) {
            $exists = DB::table('roles')->where('name', $roleName)->exists();
            if (!$exists) {
                DB::table('roles')->insert([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'satuan_wilayah')) {
                $table->dropColumn('satuan_wilayah');
            }
            if (Schema::hasColumn('users', 'jabatan_pju')) {
                $table->dropColumn('jabatan_pju');
            }
        });
    }
};
