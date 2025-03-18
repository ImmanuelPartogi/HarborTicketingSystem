<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusExpiryDateToRoutesAndScheduleDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->timestamp('status_expiry_date')->nullable()->after('status_reason');
        });

        Schema::table('schedule_dates', function (Blueprint $table) {
            $table->timestamp('status_expiry_date')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn('status_expiry_date');
        });

        Schema::table('schedule_dates', function (Blueprint $table) {
            $table->dropColumn('status_expiry_date');
        });
    }
}
