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
        Schema::create('custom_form_responses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('form_id')->constrained('custom_forms')->cascadeOnDelete();
            $table->foreignId('personel_id')->constrained('personels')->cascadeOnDelete();
            $table->dateTime('submitted_at');
            $table->string('status')->default('SUBMITTED'); // SUBMITTED, VERIFIED, ACCEPTED, REJECTED

            // Berkas Persyaratan Yang Diunggah (Metadata & Storage Paths)
            $table->json('uploaded_files')->nullable();

            // Jawaban Kuesioner (Terenkripsi AES-256-CBC pada Basis Data)
            $table->longText('answers')->nullable();

            // Hasil Verifikasi / Seleksi
            $table->text('verification_notes')->nullable();
            $table->string('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();

            $table->timestamps();

            // Cegah Pengisian Ganda untuk Formulir yang Sama oleh Personel yang Sama
            $table->unique(['form_id', 'personel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_form_responses');
    }
};
