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
        Schema::create('clearance_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clearance_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('officer_id')->nullable()->constrained('users')->onDelete('set null'); // Who approved/rejected
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Ensure one approval record per clearance per department
            $table->unique(['clearance_id', 'department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearance_approvals');
    }
};
