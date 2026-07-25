<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('broadcast_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained('broadcasts')->onDelete('cascade');
            $table->foreignId('personel_id')->constrained('personels')->onDelete('cascade');
            $table->enum('status_attendance', ['HADIR', 'TIDAK_HADIR', 'IZIN'])->index();
            $table->text('notes')->nullable();
            $table->string('permit_letter')->nullable(); // Path file dokumen izin
            $table->timestamp('responded_at')->index();
            $table->timestamps();
            $table->unique(['broadcast_id', 'personel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_responses');
    }
};