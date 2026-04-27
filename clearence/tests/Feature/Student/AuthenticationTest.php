<?php

namespace Tests\Feature\Student;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\FeatureTestCase;

class AuthenticationTest extends FeatureTestCase
{
    // ─── login ──────────────────────────────────────────────────────────────────

    public function test_student_can_log_in_with_valid_credentials(): void
    {
        // UserFactory hashes 'password' as the default; set explicitly for clarity.
        $student = $this->createStudent([
            'email'    => 'login@must.ac.tz',
            'password' => Hash::make('Secret@123'),
        ]);

        $this->post('/login', [
            'email'    => 'login@must.ac.tz',
            'password' => 'Secret@123',
        ])->assertRedirect('/student/dashboard');

        $this->assertAuthenticatedAs($student);
    }

    public function test_student_is_redirected_to_student_dashboard_not_admin(): void
    {
        $student = $this->createStudent([
            'email'    => 'student@must.ac.tz',
            'password' => Hash::make('Secret@123'),
        ]);

        $this->post('/login', [
            'email'    => 'student@must.ac.tz',
            'password' => 'Secret@123',
        ])->assertRedirect('/student/dashboard');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->createStudent(['email' => 'test@must.ac.tz']);

        $this->post('/login', [
            'email'    => 'test@must.ac.tz',
            'password' => 'WrongPassword!1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $this->post('/login', [
            'email'    => 'nobody@must.ac.tz',
            'password' => 'Secret@123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->post('/login', [])
            ->assertSessionHasErrors(['email', 'password']);
    }

    // ─── logout ─────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_log_out(): void
    {
        $student = $this->createStudent();

        $this->actingAs($student)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    // ─── account lockout (Phase 1 — G10, NOT YET IMPLEMENTED) ──────────────────
    //
    // Spec §4.1.1 §10: after N failed attempts the account must be locked and
    // the correct password should also be refused until an admin unlocks it.
    // G10 is currently unimplemented — this test will FAIL and documents the gap.

    public function test_account_locks_after_five_failed_attempts(): void
    {
        $student = $this->createStudent([
            'email'    => 'victim@must.ac.tz',
            'password' => Hash::make('CorrectPass@1'),
        ]);

        // Five consecutive failures.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email'    => 'victim@must.ac.tz',
                'password' => 'WrongPass!99',
            ]);
        }

        // The sixth attempt — even with the correct password — must be refused
        // (account locked) rather than succeeding or returning a generic mismatch.
        $this->post('/login', [
            'email'    => 'victim@must.ac.tz',
            'password' => 'CorrectPass@1',
        ])->assertStatus(429); // Too Many Requests / account locked
    }

    // ─── forgot password (Phase 1 — G3, NOT YET IMPLEMENTED) ───────────────────
    //
    // Spec §4.1.4: a password reset email must be sent and the user must be
    // able to set a new password via the token link.
    // G3 is currently unimplemented — these tests will FAIL and document the gap.

    public function test_forgot_password_form_is_accessible(): void
    {
        $this->get('/password/email')
            ->assertStatus(200);
    }

    public function test_forgot_password_sends_reset_link_to_registered_email(): void
    {
        $student = $this->createStudent(['email' => 'forgot@must.ac.tz']);

        $this->post('/password/email', ['email' => 'forgot@must.ac.tz'])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_password_reset_form_is_accessible_via_token(): void
    {
        $student = $this->createStudent(['email' => 'reset@must.ac.tz']);
        $token   = Password::createToken($student);

        $this->get('/password/reset/' . $token)
            ->assertStatus(200);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $student = $this->createStudent(['email' => 'reset2@must.ac.tz']);
        $token   = Password::createToken($student);

        $this->post('/password/reset', [
            'token'                 => $token,
            'email'                 => 'reset2@must.ac.tz',
            'password'              => 'NewSecret@456',
            'password_confirmation' => 'NewSecret@456',
        ])->assertRedirect('/login');

        // The new password must work.
        $this->post('/login', [
            'email'    => 'reset2@must.ac.tz',
            'password' => 'NewSecret@456',
        ])->assertRedirect('/student/dashboard');
    }
}
