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

            if (!Schema::hasColumn('routes', 'status_expiry_date')) {
                $table->timestamp('status_expiry_date')->nullable()->after('status_updated_at')
                    ->comment('Tanggal saat status cuaca akan otomatis berubah menjadi ACTIVE');
            }
        });

        Schema::table('schedule_dates', function (Blueprint $table) {
            // Tambahkan kolom status_reason jika belum ada
            if (!Schema::hasColumn('schedule_dates', 'status_reason')) {
                $table->string('status_reason')->nullable()->after('status')
                    ->comment('Alasan perubahan status');
            }

            // Tambahkan kolom status_expiry_date jika belum ada
            if (!Schema::hasColumn('schedule_dates', 'status_expiry_date')) {
                $table->timestamp('status_expiry_date')->nullable()->after('status_reason')
                    ->comment('Tanggal saat status cuaca akan otomatis berubah menjadi AVAILABLE');
            }

            // Tambahkan kolom modified_by_route jika belum ada
            if (!Schema::hasColumn('schedule_dates', 'modified_by_route')) {
                $table->boolean('modified_by_route')->default(false)->after('status_expiry_date')
                    ->comment('Menandakan apakah status diubah oleh perubahan status rute');
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

            if (Schema::hasColumn('routes', 'status_expiry_date')) {
                $table->dropColumn('status_expiry_date');
            }
        });

        Schema::table('schedule_dates', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_dates', 'status_reason')) {
                $table->dropColumn('status_reason');
            }

            if (Schema::hasColumn('schedule_dates', 'status_expiry_date')) {
                $table->dropColumn('status_expiry_date');
            }

            if (Schema::hasColumn('schedule_dates', 'modified_by_route')) {
                $table->dropColumn('modified_by_route');
            }
        });
    }
};
