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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 20)->unique();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('passenger_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            $table->string('qr_code');
            $table->string('seat_number', 10)->nullable();
            $table->enum('boarding_status', ['NOT_BOARDED', 'BOARDED', 'CANCELLED', 'EXPIRED'])->default('NOT_BOARDED');
            $table->timestamp('boarding_time')->nullable();
            $table->enum('status', ['ACTIVE', 'USED', 'EXPIRED', 'CANCELLED'])->default('ACTIVE');
            $table->boolean('checked_in')->default(false);
            $table->text('watermark_data')->nullable()->comment('Data untuk watermark dinamis');
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
