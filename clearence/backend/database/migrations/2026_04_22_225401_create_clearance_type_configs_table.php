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
        Schema::create('clearance_type_configs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();           // matches clearances.clearance_type enum
            $table->string('label');                    // human-readable display name
            $table->unsignedSmallInteger('sla_hours')->default(72); // hours before escalation
            $table->timestamps();
        });

        // Seed defaults. graduation/semester: 72 h; withdrawal/transfer: 48 h (more urgent).
        DB::table('clearance_type_configs')->insert([
            ['type' => 'graduation', 'label' => 'Graduation Clearance',   'sla_hours' => 72, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'semester',   'label' => 'End of Semester',         'sla_hours' => 72, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'withdrawal', 'label' => 'Withdrawal',              'sla_hours' => 48, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'transfer',   'label' => 'Transfer',                'sla_hours' => 48, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearance_type_configs');
    }
};
