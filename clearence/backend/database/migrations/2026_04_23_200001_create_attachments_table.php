<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clearance_id')->constrained()->onDelete('cascade');
            $table->string('file_name');           // original browser filename shown to user
            $table->string('stored_path', 500);    // path on attachments disk — never exposed publicly
            $table->string('mime_type', 100);
            $table->unsignedInteger('size_bytes');
            $table->timestamp('uploaded_at');

            $table->index('clearance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
