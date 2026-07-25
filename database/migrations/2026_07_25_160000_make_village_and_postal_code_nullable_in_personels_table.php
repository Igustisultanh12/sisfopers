<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->string('village', 50)->nullable()->change();
            $table->string('postal_code', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->string('village', 50)->nullable(false)->change();
            $table->string('postal_code', 10)->nullable(false)->change();
        });
    }
};
