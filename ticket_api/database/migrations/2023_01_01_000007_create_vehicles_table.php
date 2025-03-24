<?php
// 2023_01_01_000202_create_vehicles_table.php
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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['MOTORCYCLE', 'CAR', 'BUS', 'TRUCK']);
            $table->string('license_plate', 20);
            $table->decimal('weight', 10, 2)->nullable()->comment('Berat dalam kg');
            $table->timestamps();

            $table->index('license_plate');
            $table->index(['booking_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
