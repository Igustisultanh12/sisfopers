<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('face_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personel_id')->constrained('personels')->onDelete('cascade');
            $table->longText('face_embedding'); // Menyimpan representasi matriks wajah desimal dari FaceAPI
            $table->string('verification_image');
            $table->timestamp('verified_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_verifications');
    }
};