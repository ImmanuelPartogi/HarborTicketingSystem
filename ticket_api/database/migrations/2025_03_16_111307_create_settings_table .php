<?php
// 2023_01_01_000008_create_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $defaultSettings = [
            ['key' => 'site_name', 'value' => 'Ferry Ticket System', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'contact_email', 'value' => 'contact@ferryticket.com', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'phone_number', 'value' => '+62 812 3456 7890', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'booking_expiry_hours', 'value' => '24', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'currency', 'value' => 'IDR', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'tax_percentage', 'value' => '10', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('settings')->insert($defaultSettings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
