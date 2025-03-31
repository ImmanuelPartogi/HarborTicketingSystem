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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_code', 100)->nullable()->after('transaction_id');
            $table->string('payment_url')->nullable()->after('payment_code');
            $table->text('payment_data')->nullable()->after('payment_url');
            $table->timestamp('paid_at')->nullable()->after('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_code', 'payment_url', 'payment_data', 'paid_at']);
        });
    }
};
