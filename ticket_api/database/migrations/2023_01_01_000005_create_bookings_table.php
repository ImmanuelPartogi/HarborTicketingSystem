<?php
// 2023_01_01_000200_create_bookings_table.php
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained();
            $table->date('booking_date');
            $table->unsignedInteger('passenger_count')->default(1);
            $table->unsignedInteger('vehicle_count')->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', [
                'PENDING', 'CONFIRMED', 'CANCELLED', 'COMPLETED', 'REFUNDED', 'RESCHEDULED'
            ])->default('PENDING');
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index('booking_code');
            $table->index('booking_date');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index(['schedule_id', 'booking_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
