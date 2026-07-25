<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah file_sertifikat_path di tabel riwayat_pendidikan menjadi TEXT
        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $table->text('file_sertifikat_path')->nullable()->change();
        });

        // 2. Tambahkan kolom reset password OTP ke tabel personels
        Schema::table('personels', function (Blueprint $table) {
            $table->string('reset_password_otp', 6)->nullable()->after('manual_otp_printed_at');
            $table->timestamp('reset_password_otp_expired_at')->nullable()->after('reset_password_otp');
            $table->timestamp('reset_password_otp_printed_at')->nullable()->after('reset_password_otp_expired_at');
        });

        // 3. Tambahkan default parameter settings untuk disable_whatsapp_otp
        DB::table('settings')->insertOrIgnore([
            'key' => 'disable_whatsapp_otp',
            'value' => '0',
            'group' => 'system',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $table->string('file_sertifikat_path', 255)->nullable()->change();
        });

        Schema::table('personels', function (Blueprint $table) {
            $table->dropColumn([
                'reset_password_otp',
                'reset_password_otp_expired_at',
                'reset_password_otp_printed_at'
            ]);
        });

        DB::table('settings')->where('key', 'disable_whatsapp_otp')->delete();
    }
};
