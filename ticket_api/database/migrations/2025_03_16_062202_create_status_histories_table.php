<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('statusable'); // Untuk Route, Schedule, atau ScheduleDate
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
