<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nik', 16)->unique()->index();
            $table->string('nikc', 20)->nullable()->unique()->index();
            $table->string('full_name', 150)->index();
            $table->string('pob', 50);
            $table->date('dob');
            $table->enum('gender', ['L', 'P']);
            $table->enum('matra', ['AD', 'AL', 'AU'])->index();
            $table->string('angkatan', 4)->index(); // Tahun Angkatan, e.g., 2024
            $table->string('phone_number', 20)->index();
            $table->text('address');
            $table->string('province', 50);
            $table->string('city', 50);
            $table->string('district', 50);
            $table->string('village', 50);
            $table->string('postal_code', 10);
            $table->string('photo_profile')->nullable();
            $table->string('ktp_document')->nullable();
            $table->enum('status_profile', ['BELUM_LENGKAP', 'LENGKAP'])->default('BELUM_LENGKAP');
            $table->boolean('face_verified')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personels');
    }
};