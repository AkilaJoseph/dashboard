<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('programme')->nullable()->after('student_id');   // e.g. B.Eng Telecommunication Systems
            $table->string('college')->nullable()->after('programme');      // e.g. College of ICT
            $table->string('year_of_study')->nullable()->after('college');  // e.g. Year 4
            $table->string('registration_number')->nullable()->after('year_of_study'); // e.g. 22100934340012
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['programme', 'college', 'year_of_study', 'registration_number']);
        });
    }
};
