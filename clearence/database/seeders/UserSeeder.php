<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@clearence.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create Officers for each department
        $departments = Department::all();
        foreach ($departments as $dept) {
            User::create([
                'name' => $dept->name . ' Officer',
                'email' => strtolower($dept->code) . '@clearence.com',
                'password' => Hash::make('password'),
                'role' => 'officer',
                'department_id' => $dept->id,
                'is_active' => true,
            ]);
        }

        // Create sample students
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => 'Student ' . $i,
                'email' => 'student' . $i . '@clearence.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'student_id' => 'STU' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'phone' => '0700000' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
        }
    }
}
