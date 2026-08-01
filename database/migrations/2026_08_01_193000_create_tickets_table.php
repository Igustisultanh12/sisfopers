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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('ticket_number', 50)->unique();
            $table->foreignId('personel_id')->constrained('personels')->onDelete('cascade');
            $table->enum('category', ['UBAH_FOTO', 'UBAH_DATA', 'CETAK_KTA']);
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['DIPROSES', 'DISETUJUI', 'DITOLAK', 'SELESAI'])->default('DIPROSES');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
