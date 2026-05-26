<?php

namespace Tests\Helpers;

use App\Models\Clearance;
use App\Models\ClearanceApproval;
use App\Models\Department;
use App\Models\User;

/**
 * Reusable factory helpers for the MUST Clearance test suite.
 *
 * Mixed into FeatureTestCase. Every helper accepts an $overrides array so
 * individual tests can customise attributes without rebuilding the full object.
 */
trait CreatesTestData
{
    /**
     * Create an active department. Priority is auto-derived from the current
     * row count so departments sort predictably in multi-dept tests.
     */
    protected function createDepartment(array $overrides = []): Department
    {
        $uid = strtoupper(substr(uniqid('', false), -6));

        return Department::create(array_merge([
            'name'      => 'Department ' . $uid,
            'code'      => $uid,
            'is_active' => true,
            'priority'  => (Department::max('priority') ?? 0) + 1,
        ], $overrides));
    }

    /**
     * Create a student user. student_id uses uniqid to avoid the unique
     * constraint collision when multiple students are created in one test.
     */
    protected function createStudent(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role'       => 'student',
            'student_id' => 'STU' . strtoupper(substr(uniqid('', false), -8)),
            'is_active'  => true,
        ], $overrides));
    }

    /**
     * Create a departmental officer. $department is required so the officer
     * is immediately usable for approval flow tests.
     */
    protected function createOfficer(Department $department, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role'          => 'officer',
            'department_id' => $department->id,
            'is_active'     => true,
        ], $overrides));
    }

    /**
     * Create an admin user.
     */
    protected function createAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role'      => 'admin',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Create a clearance with one pending ClearanceApproval per department.
     *
     * If $departments is empty a single default department is created
     * automatically so the helper is self-contained in single-dept tests.
     *
     * Returns the fresh clearance with approvals loaded.
     *
     * @param  Department[]  $departments
     */
    protected function createClearanceWithApprovals(
        User  $student,
        array $departments = [],
        array $overrides   = []
    ): Clearance {
        if (empty($departments)) {
            $departments = [$this->createDepartment()];
        }

        $clearance = Clearance::create(array_merge([
            'user_id'        => $student->id,
            'clearance_type' => 'graduation',
            'academic_year'  => '2025/2026',
            'semester'       => 'First',
            'status'         => 'pending',
            'submitted_at'   => now(),
        ], $overrides));

        foreach ($departments as $department) {
            ClearanceApproval::create([
                'clearance_id'  => $clearance->id,
                'department_id' => $department->id,
                'status'        => 'pending',
            ]);
        }

        return $clearance->load('approvals.department');
    }
}
