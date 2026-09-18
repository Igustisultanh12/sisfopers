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
        Schema::create('live_chat_threads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('personel_id')->constrained('personels')->cascadeOnDelete();
            $table->string('subject')->default('Pusat Bantuan & Konsultasi');
            $table->string('status')->default('OPEN'); // OPEN, CLOSED
            $table->dateTime('last_message_at')->nullable();
            $table->unsignedInteger('unread_admin')->default(0);
            $table->unsignedInteger('unread_personel')->default(0);
            $table->timestamps();
        });

        Schema::create('live_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('live_chat_threads')->cascadeOnDelete();
            $table->string('sender_type'); // PERSONEL, ADMIN, PJU, KORDINATOR
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_name')->default('Operator Dinas');
            $table->text('message')->nullable();
            $table->json('attachments')->nullable(); // Array of {name, file_path, original_name, mime_type, size}
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_chat_messages');
        Schema::dropIfExists('live_chat_threads');
    }
};
