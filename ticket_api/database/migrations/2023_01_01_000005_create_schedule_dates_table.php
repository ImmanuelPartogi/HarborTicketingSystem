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
        Schema::create('schedule_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->unsignedInteger('passenger_count')->default(0)->comment('Jumlah penumpang terdaftar');
            $table->unsignedInteger('motorcycle_count')->default(0);
            $table->unsignedInteger('car_count')->default(0);
            $table->unsignedInteger('bus_count')->default(0);
            $table->unsignedInteger('truck_count')->default(0);
            $table->enum('status', ['AVAILABLE', 'FULL', 'CANCELLED', 'DEPARTED', 'WEATHER_ISSUE'])->default('AVAILABLE');
            $table->timestamps();

            $table->unique(['schedule_id', 'date'], 'idx_schedule_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_dates');
    }
};
