<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Library', 'code' => 'LIB', 'description' => 'Library clearance for book returns', 'priority' => 1],
            ['name' => 'Hostel', 'code' => 'HOST', 'description' => 'Hostel clearance for accommodation', 'priority' => 2],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Finance clearance for fees payment', 'priority' => 3],
            ['name' => 'Faculty', 'code' => 'FAC', 'description' => 'Faculty clearance for academic requirements', 'priority' => 4],
            ['name' => 'IT Department', 'code' => 'IT', 'description' => 'IT clearance for equipment returns', 'priority' => 5],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
