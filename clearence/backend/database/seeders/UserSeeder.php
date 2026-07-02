<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'      => 'System Administrator',
            'email'     => 'admin@must.ac.tz',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // One officer per department — mapped by department code
        $officerData = [
            'MUSTSO' => ['name' => 'Baraka Msangi',       'email' => 'mustso@must.ac.tz'],
            'LIB'    => ['name' => 'Emmanuel Ngowi',       'email' => 'lib@must.ac.tz'],
            'WKSHP'  => ['name' => 'Josephine Mwakasege',  'email' => 'wkshp@must.ac.tz'],
            'LAB'    => ['name' => 'Dr. Rashid Kilindoni', 'email' => 'lab@must.ac.tz'],
            'HOD'    => ['name' => 'Dr. Daniel Sinkonde',  'email' => 'hod@must.ac.tz'],
            'CAT'    => ['name' => 'Grace Mwamburi',       'email' => 'cat@must.ac.tz'],
            'SPT'    => ['name' => 'James Mlowe',          'email' => 'spt@must.ac.tz'],
            'HOST'   => ['name' => 'Amina Kibona',         'email' => 'host@must.ac.tz'],
            'HSG'    => ['name' => 'Rehema Chiwanga',      'email' => 'hsg@must.ac.tz'],
            'FIN'    => ['name' => 'Fatuma Ally',          'email' => 'fin@must.ac.tz'],
        ];

        foreach (Department::all() as $dept) {
            $data = $officerData[$dept->code] ?? [
                'name'  => $dept->name . ' Officer',
                'email' => strtolower($dept->code) . '@must.ac.tz',
            ];
            User::create([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'password'      => Hash::make('password'),
                'role'          => 'officer',
                'department_id' => $dept->id,
                'is_active'     => true,
            ]);
        }

        // Sample students — mix of degree and diploma to demonstrate Catering rule
        $students = [
            [
                'name'                => 'Daudi Kasimu Juma',
                'email'               => 'student1@must.ac.tz',
                'student_id'          => 'UE/BETS/25/14498',
                'registration_number' => '22100934340012',
                'programme'           => 'B.Eng in Telecommunication Systems',
                'college'             => 'College of Information and Communication Technology',
                'year_of_study'       => 'Year 4',
                'phone'               => '+255712000001',
            ],
            [
                'name'                => 'Mary Josephat Mwanga',
                'email'               => 'student2@must.ac.tz',
                'student_id'          => 'UE/BCOM/25/11203',
                'registration_number' => '22100934340021',
                'programme'           => 'Diploma in Computer Science',
                'college'             => 'College of Information and Communication Technology',
                'year_of_study'       => 'Year 2',
                'phone'               => '+255712000002',
            ],
            [
                'name'                => 'Hamisi Said Lema',
                'email'               => 'student3@must.ac.tz',
                'student_id'          => 'UE/BCE/25/10345',
                'registration_number' => '22100934340033',
                'programme'           => 'B.Eng in Civil Engineering',
                'college'             => 'College of Engineering and Technology',
                'year_of_study'       => 'Year 4',
                'phone'               => '+255712000003',
            ],
            [
                'name'                => 'Zuhura Hassan Musa',
                'email'               => 'student4@must.ac.tz',
                'student_id'          => 'UE/BEM/25/10789',
                'registration_number' => '22100934340044',
                'programme'           => 'Diploma in Electrical Engineering',
                'college'             => 'College of Engineering and Technology',
                'year_of_study'       => 'Year 2',
                'phone'               => '+255712000004',
            ],
            [
                'name'                => 'Peter Ndunguru Mlay',
                'email'               => 'student5@must.ac.tz',
                'student_id'          => 'UE/BIT/25/12567',
                'registration_number' => '22100934340055',
                'programme'           => 'B.Sc in Information Technology',
                'college'             => 'College of Information and Communication Technology',
                'year_of_study'       => 'Year 3',
                'phone'               => '+255712000005',
            ],
        ];

        foreach ($students as $student) {
            User::create(array_merge($student, [
                'password'  => Hash::make('password'),
                'role'      => 'student',
                'is_active' => true,
            ]));
        }
    }
}
