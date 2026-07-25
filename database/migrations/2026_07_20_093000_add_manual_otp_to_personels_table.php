<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->string('manual_otp', 6)->nullable()->after('face_verified');
            $table->timestamp('manual_otp_expired_at')->nullable()->after('manual_otp');
        });
    }

    public function down(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->dropColumn(['manual_otp', 'manual_otp_expired_at']);
        });
    }
};
