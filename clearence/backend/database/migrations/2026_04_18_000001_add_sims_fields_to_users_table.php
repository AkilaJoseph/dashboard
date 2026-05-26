<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('admission_number')->nullable()->after('registration_number');
            $table->string('entry_year')->nullable()->after('admission_number');
            $table->string('entry_programme')->nullable()->after('entry_year');
            $table->string('entry_category')->nullable()->after('entry_programme');
            $table->string('gender')->nullable()->after('entry_category');
            $table->date('birth_date')->nullable()->after('gender');
            $table->string('nationality')->nullable()->after('birth_date');
            $table->string('disability')->nullable()->after('nationality');
            $table->string('campus')->nullable()->after('disability');
            $table->timestamp('sims_synced_at')->nullable()->after('campus');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name','middle_name','last_name','admission_number',
                'entry_year','entry_programme','entry_category','gender',
                'birth_date','nationality','disability','campus','sims_synced_at',
            ]);
        });
    }
};
