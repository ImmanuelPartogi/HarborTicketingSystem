<?php
// 2023_01_01_000301_create_refunds_table.php
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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained();
            $table->foreignId('payment_id')->constrained();
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'COMPLETED'])->default('PENDING');
            $table->foreignId('refunded_by')->nullable()->constrained('admins')->onDelete('set null')
                ->comment('ID admin yang memproses');
            $table->string('transaction_id', 100)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['booking_id', 'status']);
            $table->index(['payment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
