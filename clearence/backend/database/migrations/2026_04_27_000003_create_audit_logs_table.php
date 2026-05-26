<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 60);
            $table->string('auditable_type', 255)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id'], 'idx_auditable');
            $table->index('user_id', 'idx_audit_user');
            $table->index('event',   'idx_audit_event');
            $table->index('created_at', 'idx_audit_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
