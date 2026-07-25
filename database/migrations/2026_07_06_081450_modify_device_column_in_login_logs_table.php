<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->text('device')->nullable()->change(); // Mengubah tipe data menjadi TEXT
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->string('device', 50)->nullable()->change();
        });
    }
};