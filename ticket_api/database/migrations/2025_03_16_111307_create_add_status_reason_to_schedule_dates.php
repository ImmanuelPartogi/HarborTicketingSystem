<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_dates', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_dates', 'status_reason')) {
                $table->string('status_reason')->nullable()->after('status')
                    ->comment('Alasan perubahan status');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_dates', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_dates', 'status_reason')) {
                $table->dropColumn('status_reason');
            }
        });
    }
};
