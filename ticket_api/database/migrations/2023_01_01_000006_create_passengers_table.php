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
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('id_number', 30)->comment('Nomor KTP/SIM/Paspor');
            $table->enum('id_type', ['KTP', 'SIM', 'PASPOR']);
            $table->date('dob')->comment('Tanggal Lahir');
            $table->enum('gender', ['MALE', 'FEMALE']);
            $table->boolean('is_primary')->default(false)->comment('Penumpang Utama');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passengers');
    }
};
