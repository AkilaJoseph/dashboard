<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Official MUST clearance sequence — students must clear in this exact order.
        // Catering (priority 6) is only applied to diploma students; degree students skip it.
        $departments = [
            [
                'name'        => 'MUSTSO',
                'code'        => 'MUSTSO',
                'description' => 'MUST Students Organisation — confirm no outstanding obligations to the student body.',
                'priority'    => 1,
                'is_active'   => true,
                'access_pin'  => Hash::make('1111'),
            ],
            [
                'name'        => 'Library',
                'code'        => 'LIB',
                'description' => 'Confirm return of all borrowed books, equipment, and library resources.',
                'priority'    => 2,
                'is_active'   => true,
                'access_pin'  => Hash::make('2222'),
            ],
            [
                'name'        => 'Departmental Workshops',
                'code'        => 'WKSHP',
                'description' => 'Confirm return of all tools and equipment borrowed from departmental workshops.',
                'priority'    => 3,
                'is_active'   => true,
                'access_pin'  => Hash::make('3333'),
            ],
            [
                'name'        => 'Departmental Laboratories',
                'code'        => 'LAB',
                'description' => 'Confirm return of all laboratory equipment and settlement of breakage fees.',
                'priority'    => 4,
                'is_active'   => true,
                'access_pin'  => Hash::make('4444'),
            ],
            [
                'name'        => 'Head of Department',
                'code'        => 'HOD',
                'description' => 'Department Head confirms the student has met all academic requirements for their programme.',
                'priority'    => 5,
                'is_active'   => true,
                'access_pin'  => Hash::make('5555'),
            ],
            [
                'name'        => 'Catering Office',
                'code'        => 'CAT',
                'description' => 'Confirm settlement of all catering/meal plan dues. Applicable to diploma students only.',
                'priority'    => 6,
                'is_active'   => true,
                'access_pin'  => Hash::make('6666'),
            ],
            [
                'name'        => 'Sports and Games',
                'code'        => 'SPT',
                'description' => 'Confirm return of sports equipment and clearance of any sports-related obligations.',
                'priority'    => 7,
                'is_active'   => true,
                'access_pin'  => Hash::make('7777'),
            ],
            [
                'name'        => 'Accommodation',
                'code'        => 'HOST',
                'description' => 'Confirm vacation of university accommodation and return of room keys and inventory.',
                'priority'    => 8,
                'is_active'   => true,
                'access_pin'  => Hash::make('8888'),
            ],
            [
                'name'        => 'Head of Students Governance',
                'code'        => 'HSG',
                'description' => 'Head of Student Governance confirms no disciplinary matters are pending.',
                'priority'    => 9,
                'is_active'   => true,
                'access_pin'  => Hash::make('9999'),
            ],
            [
                'name'        => 'Account Office',
                'code'        => 'FIN',
                'description' => 'Verify payment of all outstanding fees, loans, and financial obligations to the university.',
                'priority'    => 10,
                'is_active'   => true,
                'access_pin'  => Hash::make('0000'),
            ],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }

        // Deactivate legacy departments replaced by the new MUST sequence
        Department::whereIn('code', ['DEPT', 'REG'])->update(['is_active' => false]);
    }
}
