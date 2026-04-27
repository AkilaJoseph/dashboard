<?php

namespace Tests\Feature\Student;

use App\Models\Clearance;
use App\Models\Department;
use App\Services\PredictionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\FeatureTestCase;

class ClearanceLifecycleTest extends FeatureTestCase
{
    // ─── shared helpers ─────────────────────────────────────────────────────────

    /**
     * Stub PredictionService so the show-view @inject does not issue
     * MySQL-specific SQL (TIMESTAMPDIFF / DAYOFWEEK) against SQLite.
     *
     * This is a test-infrastructure stub — application code is untouched.
     * The underlying MySQL-incompatibility is reported as Bug #PS-1 below.
     */
    private function mockPredictionService(): void
    {
        $this->mock(PredictionService::class, function ($mock) {
            $mock->shouldReceive('estimateCompletion')
                ->andReturn([
                    'estimated_completion_at'  => null,
                    'confidence_level'         => 'insufficient_data',
                    'per_department_breakdown' => [],
                ]);
        });
    }

    /** Minimal valid clearance submission payload. */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'academic_year'  => '2025/2026',
            'semester'       => 'First',
            'clearance_type' => 'graduation',
            'reason'         => 'Completing final year requirements.',
            'declaration'    => '1',
        ], $overrides);
    }

    // ─── submission ─────────────────────────────────────────────────────────────

    public function test_student_can_submit_a_clearance_request(): void
    {
        $student = $this->createStudent();
        $dept    = $this->createDepartment();

        $this->actingAs($student)
            ->post(route('student.clearances.store'), $this->validPayload())
            ->assertRedirect(route('student.clearances.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('clearances', [
            'user_id'        => $student->id,
            'clearance_type' => 'graduation',
            'academic_year'  => '2025/2026',
            'semester'       => 'First',
            'status'         => 'pending',
        ]);

        // One approval row seeded per active department.
        $this->assertDatabaseHas('clearance_approvals', [
            'department_id' => $dept->id,
            'status'        => 'pending',
        ]);
    }

    public function test_clearance_submission_requires_declaration_checkbox(): void
    {
        $student = $this->createStudent();
        $this->createDepartment();

        $this->actingAs($student)
            ->post(route('student.clearances.store'), $this->validPayload(['declaration' => null]))
            ->assertSessionHasErrors('declaration');

        $this->assertDatabaseCount('clearances', 0);
    }

    public function test_clearance_submission_validates_academic_year_format(): void
    {
        $student = $this->createStudent();

        $this->actingAs($student)
            ->post(route('student.clearances.store'), $this->validPayload(['academic_year' => '2025']))
            ->assertSessionHasErrors('academic_year');
    }

    public function test_student_can_submit_clearance_with_attachments(): void
    {
        Storage::fake('attachments');

        $student = $this->createStudent();
        $this->createDepartment();

        // fake()->image() creates a real GD-generated JPEG that passes
        // the double finfo MIME check in the controller.
        $file = UploadedFile::fake()->image('transcript.jpg');

        $this->actingAs($student)
            ->post(route('student.clearances.store'), array_merge(
                $this->validPayload(),
                ['files' => [$file]]
            ))
            ->assertRedirect(route('student.clearances.index'));

        $this->assertDatabaseHas('attachments', [
            'file_name' => 'transcript.jpg',
            'mime_type' => 'image/jpeg',
        ]);
    }

    public function test_submission_rejects_more_than_five_attachments(): void
    {
        $student = $this->createStudent();
        $files   = [];

        for ($i = 0; $i < 6; $i++) {
            $files[] = UploadedFile::fake()->image("doc{$i}.jpg");
        }

        $this->actingAs($student)
            ->post(route('student.clearances.store'), array_merge(
                $this->validPayload(),
                ['files' => $files]
            ))
            ->assertSessionHasErrors('files');
    }

    // ─── duplicate-submission guard (NOT YET IMPLEMENTED) ───────────────────────
    //
    // Spec §4.1.2: a student may not submit a new clearance request while an
    // existing one is pending or in progress. The controller currently has no
    // such guard — this test will FAIL and documents the gap.

    public function test_student_cannot_submit_second_clearance_while_one_is_in_progress(): void
    {
        $student = $this->createStudent();
        $dept    = $this->createDepartment();

        // First clearance already in-progress.
        $this->createClearanceWithApprovals($student, [$dept], ['status' => 'in_progress']);

        $this->actingAs($student)
            ->post(route('student.clearances.store'), $this->validPayload())
            ->assertRedirect(route('student.clearances.create'))
            ->assertSessionHasErrors();

        // Only the one pre-existing clearance should exist.
        $this->assertDatabaseCount('clearances', 1);
    }

    // ─── show / status view ─────────────────────────────────────────────────────

    public function test_student_sees_per_department_status_on_show_page(): void
    {
        $this->mockPredictionService();
        $student = $this->createStudent();
        $dept    = $this->createDepartment(['name' => 'Library Department']);
        $clearance = $this->createClearanceWithApprovals($student, [$dept]);

        $this->actingAs($student)
            ->get(route('student.clearances.show', $clearance))
            ->assertStatus(200)
            ->assertSee('Library Department');
    }

    public function test_show_page_displays_pending_status_badge(): void
    {
        $this->mockPredictionService();
        $student   = $this->createStudent();
        $dept      = $this->createDepartment();
        $clearance = $this->createClearanceWithApprovals($student, [$dept]);

        $this->actingAs($student)
            ->get(route('student.clearances.show', $clearance))
            ->assertStatus(200)
            ->assertSee('Pending', false); // case-sensitive match in badge
    }

    // ─── authorisation — ownership ──────────────────────────────────────────────

    public function test_student_cannot_view_another_students_clearance(): void
    {
        $studentA  = $this->createStudent();
        $studentB  = $this->createStudent();
        $dept      = $this->createDepartment();
        $clearance = $this->createClearanceWithApprovals($studentA, [$dept]);

        $this->actingAs($studentB)
            ->get(route('student.clearances.show', $clearance))
            ->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected_from_show_page(): void
    {
        $student   = $this->createStudent();
        $dept      = $this->createDepartment();
        $clearance = $this->createClearanceWithApprovals($student, [$dept]);

        $this->get(route('student.clearances.show', $clearance))
            ->assertRedirect('/login');
    }

    // ─── certificate download ────────────────────────────────────────────────────

    public function test_student_cannot_download_certificate_while_status_is_pending(): void
    {
        $student   = $this->createStudent();
        $dept      = $this->createDepartment();
        $clearance = $this->createClearanceWithApprovals($student, [$dept]); // status: pending

        $this->actingAs($student)
            ->get(route('student.clearances.certificate', $clearance))
            ->assertStatus(403);
    }

    public function test_student_cannot_download_certificate_while_status_is_in_progress(): void
    {
        $student   = $this->createStudent();
        $dept      = $this->createDepartment();
        $clearance = $this->createClearanceWithApprovals(
            $student, [$dept], ['status' => 'in_progress']
        );

        $this->actingAs($student)
            ->get(route('student.clearances.certificate', $clearance))
            ->assertStatus(403);
    }

    public function test_student_cannot_download_certificate_while_status_is_rejected(): void
    {
        $student   = $this->createStudent();
        $dept      = $this->createDepartment();
        $clearance = $this->createClearanceWithApprovals(
            $student, [$dept], ['status' => 'rejected']
        );

        $this->actingAs($student)
            ->get(route('student.clearances.certificate', $clearance))
            ->assertStatus(403);
    }

    // ─── certificate download after approval (BUG — G1 incomplete) ──────────────
    //
    // The downloadCertificate() controller calls $clearance->load('finalApprover')
    // but Clearance has no finalApprover() relationship (it was planned in G1 but
    // G1 was never implemented). This test will FAIL with a 500 and documents
    // the bug. Fix required: either add a stub finalApprover() to Clearance
    // returning nulls, or implement G1 properly.

    public function test_student_can_download_certificate_after_full_approval(): void
    {
        $student   = $this->createStudent();
        $dept      = $this->createDepartment();
        $clearance = $this->createClearanceWithApprovals(
            $student, [$dept], ['status' => 'approved']
        );

        $response = $this->actingAs($student)
            ->get(route('student.clearances.certificate', $clearance));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_another_student_cannot_download_a_different_students_certificate(): void
    {
        $studentA  = $this->createStudent();
        $studentB  = $this->createStudent();
        $dept      = $this->createDepartment();
        $clearance = $this->createClearanceWithApprovals(
            $studentA, [$dept], ['status' => 'approved']
        );

        $this->actingAs($studentB)
            ->get(route('student.clearances.certificate', $clearance))
            ->assertStatus(403);
    }
}
