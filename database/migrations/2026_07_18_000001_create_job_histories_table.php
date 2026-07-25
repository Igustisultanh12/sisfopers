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
        Schema::create('job_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personel_id')->constrained('personels')->onDelete('cascade');
            $table->enum('tipe_pekerjaan', ['ASN', 'SWASTA', 'WIRASWASTA', 'PELAJAR', 'TIDAK_BEKERJA']);
            $table->string('nama_perusahaan')->nullable(); // Nama Satuan Kerja / PT / Sekolah / Bidang Usaha
            $table->string('jabatan')->nullable(); // Jabatan / Posisi Kerja / Kelas
            $table->string('nip')->nullable(); // NIP jika ASN
            $table->string('nomor_karyawan')->nullable(); // Nomor Karyawan jika Swasta / NIM Pelajar
            $table->date('tmt_mulai')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('kecamatan')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('kode_pos')->nullable();
            $table->boolean('is_phk')->default(false);
            $table->date('tmt_phk')->nullable();
            $table->text('alasan_phk')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_histories');
    }
};
