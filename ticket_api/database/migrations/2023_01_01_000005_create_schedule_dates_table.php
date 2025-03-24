<?php
// 2023_01_01_000103_create_schedule_dates_table.php
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
            $table->enum('status', [
                'AVAILABLE', 'UNAVAILABLE', 'FULL', 'CANCELLED', 'DEPARTED', 'WEATHER_ISSUE'
            ])->default('AVAILABLE');
            $table->string('status_reason')->nullable()->comment('Alasan perubahan status');
            $table->timestamp('status_expiry_date')->nullable()->comment('Tanggal saat status cuaca akan otomatis berubah');
            $table->boolean('modified_by_schedule')->default(false)->comment('Diubah oleh perubahan status jadwal');
            $table->foreignId('adjustment_id')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'date'], 'idx_schedule_date');
            $table->index('date');
            $table->index('status');
            $table->index(['date', 'status']);
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
