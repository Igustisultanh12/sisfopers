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
        if (!Schema::hasTable('ektas')) {
            Schema::create('ektas', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('personel_id')->constrained('personels')->onDelete('cascade');
                $table->string('nomor_kta')->nullable();
                $table->string('nomor_urut')->nullable();
                $table->string('tahun_lulus')->nullable();
                $table->string('pangkat')->nullable();
                $table->string('jabatan')->default('Anggota Komcad');
                $table->string('kesatuan_matra')->default('Matra Darat');
                $table->string('berlaku_sampai')->default('Selama Menjadi Anggota Komcad');
                
                // Sinyalemen
                $table->string('tinggi_berat')->nullable();
                $table->string('rambut')->nullable();
                $table->string('mata')->nullable();
                $table->string('golongan_darah')->nullable();
                $table->string('tempat_lahir')->nullable();
                $table->string('tanggal_lahir')->nullable();
                $table->string('agama')->nullable();
                $table->text('alamat')->nullable();
                $table->text('tanda_kehormatan')->nullable();

                // Verifikasi & Security
                $table->string('verify_code')->unique();
                $table->string('qr_code_url')->nullable();
                $table->enum('status', ['BELUM_DITERBITKAN', 'TERBIT', 'DIBATALKAN'])->default('BELUM_DITERBITKAN');
                $table->timestamp('issued_at')->nullable();
                $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ektas');
    }
};
