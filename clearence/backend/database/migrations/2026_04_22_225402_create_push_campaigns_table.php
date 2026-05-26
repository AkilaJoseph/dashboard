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
        Schema::create('push_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('image_url')->nullable();
            $table->string('target_url')->default('/');
            $table->json('audience');           // {"roles":["student"],"department_id":null}
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_campaigns');
    }
};
