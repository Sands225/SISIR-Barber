<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->unique(); // E.164 format, e.g. 6281234567890
            $table->string('wa_id', 30)->unique()->nullable(); // WhatsApp user ID
            $table->string('conversation_state', 50)->default('idle'); // Chatbot state machine
            $table->json('conversation_context')->nullable(); // Multi-turn context payload
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
