<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clearances', function (Blueprint $table) {
            $table->foreignId('final_approver_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clearances', function (Blueprint $table) {
            $table->dropForeign(['final_approver_id']);
            $table->dropColumn('final_approver_id');
        });
    }
};
