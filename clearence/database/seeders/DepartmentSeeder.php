<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name'        => 'Finance & Accounts Office',
                'code'        => 'FIN',
                'description' => 'Verify payment of all outstanding fees, loans, and financial obligations to the university.',
                'priority'    => 1,
                'is_active'   => true,
            ],
            [
                'name'        => 'University Library',
                'code'        => 'LIB',
                'description' => 'Confirm return of all borrowed books, equipment, and library resources.',
                'priority'    => 2,
                'is_active'   => true,
            ],
            [
                'name'        => 'Hostel & Accommodation',
                'code'        => 'HOST',
                'description' => 'Confirm vacation of university accommodation and return of room keys and inventory.',
                'priority'    => 3,
                'is_active'   => true,
            ],
            [
                'name'        => 'Academic Department',
                'code'        => 'DEPT',
                'description' => 'Department Head confirms the student has met all academic requirements for their programme.',
                'priority'    => 4,
                'is_active'   => true,
            ],
            [
                'name'        => 'Sports & Games',
                'code'        => 'SPT',
                'description' => 'Confirm return of sports equipment and clearance of any sports-related obligations.',
                'priority'    => 5,
                'is_active'   => true,
            ],
            [
                'name'        => 'Registry Office',
                'code'        => 'REG',
                'description' => 'Final academic registry clearance; confirms student records are complete and up to date.',
                'priority'    => 6,
                'is_active'   => true,
            ],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
