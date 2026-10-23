<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mobile_devices')) {
            return;
        }

        Schema::create('mobile_devices', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type', 100);
            $table->unsignedBigInteger('tokenable_id');
            $table->string('fcm_token', 255)->unique();
            $table->string('platform', 20)->default('android');
            $table->string('device_name', 120)->nullable();
            $table->string('app_version', 30)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['tokenable_type', 'tokenable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_devices');
    }
};
