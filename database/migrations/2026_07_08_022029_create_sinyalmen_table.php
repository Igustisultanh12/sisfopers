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
        Schema::create('sinyalmen', function (Blueprint $table) {
            $table->id();
            // Menautkan relasi foreign key secara ketat ke tabel personel
            $table->foreignId('personel_id')->constrained('personels')->onDelete('cascade');
            $table->integer('tinggi_badan')->nullable();
            $table->integer('berat_badan')->nullable();
            $table->string('golongan_darah', 5)->nullable();
            $table->string('rambut')->nullable();
            $table->string('mata')->nullable();
            $table->string('ciri_khas')->nullable();
            $table->string('cacat_tubuh')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinyalmen');
    }
};