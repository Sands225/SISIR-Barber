<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barber_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained('barbers')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sun, 1=Mon ... 6=Sat
            $table->time('open_time');
            $table->time('close_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // A barber can only have one schedule entry per day
            $table->unique(['barber_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_schedules');
    }
};
