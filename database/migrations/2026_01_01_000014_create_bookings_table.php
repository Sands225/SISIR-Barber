<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('barber_id')->constrained('barbers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('status', 30)->default('TEMP_LOCKED');
            // status ENUM values:
            //   TEMP_LOCKED | BOOKED | CONFIRMED | IN_SERVICE | COMPLETED | CANCELLED_BY_SYSTEM | NO_SHOW

            $table->unsignedInteger('dp_amount')->default(0); // IDR
            $table->string('midtrans_order_id', 100)->unique()->nullable();
            $table->string('midtrans_transaction_id', 100)->nullable();
            $table->string('midtrans_payment_type', 50)->nullable();
            $table->string('midtrans_qr_code_url')->nullable();
            $table->dateTime('lock_expires_at')->nullable(); // TEMP_LOCKED TTL
            $table->text('notes')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // ── DB-Level Double-Booking Prevention ──────────────────────────
            // Prevents two bookings for the same barber at the exact same time slot
            $table->unique(['barber_id', 'scheduled_at'], 'unique_barber_slot');

            $table->index(['status', 'scheduled_at']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
