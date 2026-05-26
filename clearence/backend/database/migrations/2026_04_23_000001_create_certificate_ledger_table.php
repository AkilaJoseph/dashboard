<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clearance_id')
                  ->unique()
                  ->constrained('clearances')
                  ->onDelete('cascade');
            $table->char('certificate_hash', 64);
            $table->char('previous_hash', 64);
            $table->unsignedBigInteger('sequence');
            $table->timestamp('signed_at')->useCurrent();
            $table->text('signature');

            $table->index('sequence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_ledger');
    }
};
