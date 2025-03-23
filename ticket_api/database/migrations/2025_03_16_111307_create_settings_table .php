<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $defaultSettings = [
            ['key' => 'site_name', 'value' => 'Ferry Ticket System'],
            ['key' => 'contact_email', 'value' => 'contact@ferryticket.com'],
            ['key' => 'phone_number', 'value' => '+62 812 3456 7890'],
            ['key' => 'booking_expiry_hours', 'value' => '24'],
            ['key' => 'currency', 'value' => 'IDR'],
            ['key' => 'tax_percentage', 'value' => '10'],
        ];

        DB::table('settings')->insert($defaultSettings);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
