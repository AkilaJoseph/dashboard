<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clearances', function (Blueprint $table) {
            $table->string('reference_no', 50)->nullable()->unique()->after('id');
        });

        // Back-fill existing rows that were created before this column existed.
        DB::table('clearances')->whereNull('reference_no')->orderBy('id')->each(function ($row) {
            $year = date('Y', strtotime($row->submitted_at ?? now()));
            DB::table('clearances')->where('id', $row->id)->update([
                'reference_no' => 'CLR/' . $year . '/' . str_pad($row->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('clearances', function (Blueprint $table) {
            $table->dropUnique(['reference_no']);
            $table->dropColumn('reference_no');
        });
    }
};
