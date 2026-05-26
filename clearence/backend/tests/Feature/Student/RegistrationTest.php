<?php

namespace Tests\Feature\Student;

use Tests\FeatureTestCase;

class RegistrationTest extends FeatureTestCase
{
    // ─── helpers ────────────────────────────────────────────────────────────────

    /** Full valid registration payload. Tests override individual keys. */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Daudi Kasimu Juma',
            'email'                 => 'daudi@must.ac.tz',
            'phone'                 => '0712000001',
            'student_id'            => 'STU2025001',
            'registration_number'   => '22100934340012',
            'programme'             => 'B.Eng Telecommunication Systems',
            'college'               => 'College of ICT',
            'year_of_study'         => 'Year 4',
            'password'              => 'Secret@123',
            'password_confirmation' => 'Secret@123',
        ], $overrides);
    }

    // ─── happy path ─────────────────────────────────────────────────────────────

    public function test_student_can_register_with_valid_data(): void
    {
        $this->post('/register', $this->validPayload())
            ->assertRedirect(route('student.dashboard'));

        $this->assertDatabaseHas('users', [
            'email'      => 'daudi@must.ac.tz',
            'role'       => 'student',
            // RegisterController calls strtoupper() before persisting.
            'student_id' => 'STU2025001',
        ]);
    }

    public function test_newly_registered_student_is_authenticated(): void
    {
        $this->post('/register', $this->validPayload());

        $this->assertAuthenticated();
    }

    // ─── strong password (Phase 1 — StrongPassword rule) ────────────────────────

    public function test_registration_rejects_password_with_no_uppercase_letter(): void
    {
        $this->post('/register', $this->validPayload([
            'password'              => 'secret@123',
            'password_confirmation' => 'secret@123',
        ]))->assertSessionHasErrors('password');
    }

    public function test_registration_rejects_password_with_no_lowercase_letter(): void
    {
        $this->post('/register', $this->validPayload([
            'password'              => 'SECRET@123',
            'password_confirmation' => 'SECRET@123',
        ]))->assertSessionHasErrors('password');
    }

    public function test_registration_rejects_password_with_no_digit(): void
    {
        $this->post('/register', $this->validPayload([
            'password'              => 'Secret@abc',
            'password_confirmation' => 'Secret@abc',
        ]))->assertSessionHasErrors('password');
    }

    public function test_registration_rejects_password_with_no_special_character(): void
    {
        $this->post('/register', $this->validPayload([
            'password'              => 'Secret123',
            'password_confirmation' => 'Secret123',
        ]))->assertSessionHasErrors('password');
    }

    public function test_registration_rejects_password_shorter_than_eight_characters(): void
    {
        $this->post('/register', $this->validPayload([
            'password'              => 'Se@1',
            'password_confirmation' => 'Se@1',
        ]))->assertSessionHasErrors('password');
    }

    public function test_registration_rejects_mismatched_password_confirmation(): void
    {
        $this->post('/register', $this->validPayload([
            'password'              => 'Secret@123',
            'password_confirmation' => 'Different@456',
        ]))->assertSessionHasErrors('password');
    }

    // ─── unique-field guards ─────────────────────────────────────────────────────

    public function test_registration_rejects_duplicate_email(): void
    {
        $this->createStudent(['email' => 'daudi@must.ac.tz']);

        $this->post('/register', $this->validPayload(['email' => 'daudi@must.ac.tz']))
            ->assertSessionHasErrors('email');
    }

    public function test_registration_rejects_duplicate_student_id(): void
    {
        $this->createStudent(['student_id' => 'STU2025001']);

        $this->post('/register', $this->validPayload(['student_id' => 'STU2025001']))
            ->assertSessionHasErrors('student_id');
    }

    // ─── required-field guards ───────────────────────────────────────────────────

    public function test_registration_requires_all_mandatory_fields(): void
    {
        $this->post('/register', [])
            ->assertSessionHasErrors([
                'name', 'email', 'student_id',
                'programme', 'college', 'year_of_study', 'password',
            ]);
    }
}
