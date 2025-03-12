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
        Schema::create('ferries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('capacity_passenger')->comment('Kapasitas Penumpang');
            $table->unsignedInteger('capacity_vehicle_motorcycle')->comment('Kapasitas Motor');
            $table->unsignedInteger('capacity_vehicle_car')->comment('Kapasitas Mobil');
            $table->unsignedInteger('capacity_vehicle_bus')->comment('Kapasitas Bus');
            $table->unsignedInteger('capacity_vehicle_truck')->comment('Kapasitas Truk');
            $table->enum('status', ['ACTIVE', 'MAINTENANCE', 'INACTIVE'])->default('ACTIVE');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ferries');
    }
};
