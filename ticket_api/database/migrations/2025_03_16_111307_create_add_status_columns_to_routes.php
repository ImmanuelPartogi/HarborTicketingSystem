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
        Schema::table('routes', function (Blueprint $table) {
            // Add status_reason column if it doesn't exist
            if (!Schema::hasColumn('routes', 'status_reason')) {
                $table->string('status_reason')->nullable()->after('status')
                    ->comment('Alasan perubahan status');
            }

            // Add status_updated_at column if it doesn't exist
            if (!Schema::hasColumn('routes', 'status_updated_at')) {
                $table->timestamp('status_updated_at')->nullable()->after('status_reason')
                    ->comment('Waktu terakhir status diperbarui');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            if (Schema::hasColumn('routes', 'status_reason')) {
                $table->dropColumn('status_reason');
            }

            if (Schema::hasColumn('routes', 'status_updated_at')) {
                $table->dropColumn('status_updated_at');
            }
        });
    }
};
