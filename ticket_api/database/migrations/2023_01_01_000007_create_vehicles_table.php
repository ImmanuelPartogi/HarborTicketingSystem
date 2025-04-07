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
        // Jika ingin mengubah tabel yang sudah ada
        Schema::table('vehicles', function (Blueprint $table) {
            // Ubah kolom owner_passenger_id menjadi nullable jika belum
            $table->foreignId('owner_passenger_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback jika diperlukan
    }
};
