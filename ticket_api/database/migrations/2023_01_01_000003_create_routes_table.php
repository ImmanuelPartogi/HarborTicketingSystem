<?php
// 2023_01_01_000101_create_routes_table.php
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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('origin')->comment('Pelabuhan Asal');
            $table->string('destination')->comment('Pelabuhan Tujuan');
            $table->decimal('distance', 10, 2)->nullable()->comment('Jarak dalam KM');
            $table->unsignedInteger('duration')->comment('Durasi dalam menit');
            $table->decimal('base_price', 12, 2)->comment('Harga dasar untuk penumpang');
            $table->decimal('motorcycle_price', 12, 2)->comment('Harga tambahan untuk motor');
            $table->decimal('car_price', 12, 2)->comment('Harga tambahan untuk mobil');
            $table->decimal('bus_price', 12, 2)->comment('Harga tambahan untuk bus');
            $table->decimal('truck_price', 12, 2)->comment('Harga tambahan untuk truk');
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'WEATHER_ISSUE'])->default('ACTIVE');
            $table->string('status_reason')->nullable()->comment('Alasan perubahan status');
            $table->timestamp('status_updated_at')->nullable()->comment('Waktu terakhir status diperbarui');
            $table->timestamp('status_expiry_date')->nullable()->comment('Tanggal saat status cuaca akan otomatis berubah');
            $table->timestamps();

            $table->unique(['origin', 'destination'], 'idx_origin_destination');
            $table->index('status');
            $table->index('origin');
            $table->index('destination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
