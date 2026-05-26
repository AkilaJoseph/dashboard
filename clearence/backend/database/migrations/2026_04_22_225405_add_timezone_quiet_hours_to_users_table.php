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
        Schema::table('users', function (Blueprint $table) {
            // Africa/Dar_es_Salaam = UTC+3, the institution timezone for MUST, Tanzania
            $table->string('timezone', 50)->default('Africa/Dar_es_Salaam')->after('notification_preferences');
            $table->time('quiet_hours_start')->default('22:00:00')->after('timezone');
            $table->time('quiet_hours_end')->default('06:00:00')->after('quiet_hours_start');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'quiet_hours_start', 'quiet_hours_end']);
        });
    }
};
