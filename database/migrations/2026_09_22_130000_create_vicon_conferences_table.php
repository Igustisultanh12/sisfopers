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
        Schema::create('vicon_rooms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('room_code')->unique()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('host_user_id')->nullable();
            $table->string('status')->default('ACTIVE'); // SCHEDULED, ACTIVE, ENDED
            $table->string('agora_channel')->unique();
            $table->boolean('allow_guest')->default(true);
            $table->string('guest_passcode')->nullable();
            $table->unsignedInteger('max_participants')->default(50);
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('host_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('vicon_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('vicon_rooms')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->string('display_name');
            $table->string('role')->default('PERSONEL'); // HOST, PERSONEL, GUEST
            $table->unsignedBigInteger('agora_uid')->index();
            $table->string('guest_token')->nullable()->index();
            $table->string('status')->default('INVITED'); // INVITED, JOINED, LEFT
            $table->dateTime('invited_at')->nullable();
            $table->dateTime('joined_at')->nullable();
            $table->dateTime('left_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('personel_id')->references('id')->on('personels')->nullOnDelete();
        });

        Schema::create('vicon_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('vicon_rooms')->cascadeOnDelete();
            $table->string('sender_name');
            $table->string('sender_type')->default('GUEST'); // HOST, PERSONEL, GUEST
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vicon_messages');
        Schema::dropIfExists('vicon_participants');
        Schema::dropIfExists('vicon_rooms');
    }
};
