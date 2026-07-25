<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pengkinian_data')) {
            Schema::create('pengkinian_data', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('personel_id')->constrained('personels')->onDelete('cascade');
                $table->enum('jenis_pengkinian', ['MENINGGAL', 'TNI_AD', 'TNI_AL', 'TNI_AU', 'POLRI'])->index();
                $table->string('document_path');
                $table->string('nrp', 50)->nullable();
                $table->date('tmt_pengangkatan')->nullable();
                $table->date('tmt_masuk_satuan')->nullable();
                $table->string('satuan', 150)->nullable();
                $table->string('jabatan', 150)->nullable();
                $table->text('catatan')->nullable();
                $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING')->index();
                $table->text('rejection_reason')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('personels', function (Blueprint $table) {
            if (!Schema::hasColumn('personels', 'status_keaktifan')) {
                $table->enum('status_keaktifan', ['AKTIF', 'MENINGGAL', 'TNI_AD', 'TNI_AL', 'TNI_AU', 'POLRI'])->default('AKTIF')->after('face_verified')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengkinian_data');
        Schema::table('personels', function (Blueprint $table) {
            if (Schema::hasColumn('personels', 'status_keaktifan')) {
                $table->dropColumn('status_keaktifan');
            }
        });
    }
};
