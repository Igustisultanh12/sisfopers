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
        Schema::create('skep_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique()->index();
            $table->string('nama_lengkap');
            $table->string('nik', 16)->index();
            $table->string('nikc')->index();
            $table->string('angkatan');
            $table->string('matra'); // AD, AL, AU
            $table->string('phone_number');
            $table->string('skep_file'); // Path file PDF
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING')->index();
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skep_requests');
    }
};
