<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clearance_approvals', function (Blueprint $table) {
            // Composite index used by PredictionService per-department avg queries.
            // Covers: WHERE department_id = ? AND reviewed_at IS NOT NULL AND reviewed_at >= ?
            $table->index(['department_id', 'reviewed_at'], 'ca_dept_reviewed_idx');
        });
    }

    public function down(): void
    {
        Schema::table('clearance_approvals', function (Blueprint $table) {
            $table->dropIndex('ca_dept_reviewed_idx');
        });
    }
};
