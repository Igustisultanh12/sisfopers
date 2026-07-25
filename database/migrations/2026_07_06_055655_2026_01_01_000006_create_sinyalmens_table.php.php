<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sinyalmens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personel_id')->constrained('personels')->onDelete('cascade');
            $table->integer('height_cm');
            $table->integer('weight_kg');
            $table->string('hair_color', 30);
            $table->string('eye_color', 30);
            $table->enum('blood_type', ['A', 'B', 'AB', 'O'])->index();
            $table->string('religion', 30);
            $table->string('marital_status', 30);
            $table->string('full_body_photo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinyalmens');
    }
};