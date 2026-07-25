<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title', 200);
            $table->string('category', 50)->index(); // Tugas, Latihan, Mobilisasi
            $table->date('event_date');
            $table->time('event_time');
            $table->string('location', 255);
            $table->text('description');
            $table->string('attachment')->nullable();
            $table->dateTime('deadline')->index();
            $table->enum('target_type', ['ALL', 'MATRA', 'ANGKATAN', 'INDIVIDUAL'])->index();
            $table->string('target_value', 100)->nullable(); // Menyimpan informasi Matra atau Angkatan jika spesifik
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('broadcast_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained('broadcasts')->onDelete('cascade');
            $table->foreignId('personel_id')->constrained('personels')->onDelete('cascade');
            $table->boolean('is_sent_wa')->default(false);
            $table->timestamp('sent_wa_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_targets');
        Schema::dropIfExists('broadcasts');
    }
};