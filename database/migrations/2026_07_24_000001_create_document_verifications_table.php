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
        Schema::create('document_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('verify_code', 50)->unique();
            $table->string('doc_type', 50);
            $table->string('doc_title', 255);
            $table->string('subject_name', 255);
            $table->string('subject_identifier', 100)->nullable();
            $table->string('signer_name', 255);
            $table->string('signer_title', 255)->nullable();
            $table->timestamp('printed_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};
