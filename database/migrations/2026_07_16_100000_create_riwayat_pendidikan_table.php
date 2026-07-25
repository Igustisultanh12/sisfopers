<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('riwayat_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personel_id')->constrained('personels')->cascadeOnDelete();
            $table->enum('jenis', ['AKADEMIK', 'DIKLAT', 'MILITER']);
            $table->enum('jenjang', ['SD', 'SMP', 'SMA', 'D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI', 'LAIN']);
            $table->string('program_studi')->nullable();
            $table->string('nama_institusi')->nullable();
            $table->integer('tahun_lulus')->nullable();
            $table->string('nomor_ijazah')->nullable();
            $table->string('file_ijazah_path')->nullable();
            $table->string('file_sertifikat_path')->nullable();
            $table->enum('ocr_status', ['IDLE', 'QUEUED', 'PROCESSING', 'DONE', 'FAILED'])->default('IDLE')->index();
            $table->string('ocr_text_path')->nullable();
            $table->decimal('ocr_confidence', 5, 2)->nullable();
            $table->text('ocr_error')->nullable();
            $table->unsignedBigInteger('sk_penetapan_id')->nullable();
            $table->string('front_title')->nullable();
            $table->string('suffix_gelar')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(
                ['personel_id', 'jenis', 'jenjang', 'program_studi', 'tahun_lulus'],
                'riwayat_pendidikan_unique'
            );
            $table->unique(['personel_id', 'nomor_ijazah'], 'riwayat_pendidikan_nomor_ijazah_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pendidikan');
    }
};
