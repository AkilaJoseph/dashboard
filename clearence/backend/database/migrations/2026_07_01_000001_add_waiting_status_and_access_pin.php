<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the status enum to include 'waiting' (locked, not yet this student's turn)
        DB::statement("ALTER TABLE clearance_approvals MODIFY COLUMN status ENUM('pending','approved','rejected','waiting') NOT NULL DEFAULT 'pending'");

        // Department PIN for extra security — hashed bcrypt value stored here
        Schema::table('departments', function (Blueprint $table) {
            $table->string('access_pin')->nullable()->after('priority');
        });
    }

    public function down(): void
    {
        // Revert status enum (drop 'waiting')
        DB::statement("ALTER TABLE clearance_approvals MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('access_pin');
        });
    }
};
