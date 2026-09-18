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
        Schema::create('custom_forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('category')->default('REKRUTMEN');
            $table->longText('description')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->boolean('is_active')->default(true);

            // Kriteria Target Sasaran Personel
            $table->string('target_rank_category')->default('ALL'); // ALL, PERWIRA, BINTARA, TAMTAMA
            $table->string('target_matra')->default('ALL'); // ALL, AD, AL, AU
            $table->string('target_angkatan')->default('ALL'); // ALL atau tahun angkatan

            // Persyaratan Berkas & Kuesioner Pertanyaan (Format JSON)
            $table->json('requirements')->nullable();
            $table->json('questions')->nullable();

            // Identitas Pembuat (Admin, PJU, atau Koordinator)
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('created_by_pju_id')->nullable();
            $table->string('creator_role')->default('admin');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_forms');
    }
};
