<?php
// 2023_01_01_000300_create_payments_table.php
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['BANK_TRANSFER', 'VIRTUAL_ACCOUNT', 'E_WALLET', 'CREDIT_CARD']);
            $table->string('payment_channel', 50)->comment('BCA, MANDIRI, BNI, OVO, DANA, dll');
            $table->string('transaction_id', 100)->nullable()->comment('ID Transaksi dari Payment Gateway');
            $table->enum('status', [
                'PENDING', 'SUCCESS', 'FAILED', 'EXPIRED', 'REFUNDED', 'PARTIAL_REFUND'
            ])->default('PENDING');
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->timestamp('refund_date')->nullable();
            $table->text('payload')->nullable()->comment('Respon mentah dari payment gateway');
            $table->timestamps();

            $table->index('status');
            $table->index('transaction_id');
            $table->index(['booking_id', 'status']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
