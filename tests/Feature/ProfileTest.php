<?php

namespace Tests\Feature;

use App\Jobs\HardDeleteExpiredUsersJob;
use App\Models\Attempt;
use App\Models\Certificate;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Profile page display
    // -------------------------------------------------------------------------

    public function test_profile_page_requires_auth(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_profile_page_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertOk();
    }

    // -------------------------------------------------------------------------
    // Profile update
    // -------------------------------------------------------------------------

    public function test_profile_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/update', [
                'name'         => 'Updated Name',
                'phone'        => '+91 9876543210',
                'country_code' => 'IN',
                'timezone'     => 'Asia/Kolkata',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('IN', $user->country_code);
        $this->assertSame('Asia/Kolkata', $user->timezone);
    }

    public function test_profile_update_rejects_invalid_country_code(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/update', [
                'name'         => 'Name',
                'country_code' => 'ZZ', // not in CountryCodes::options()
            ])
            ->assertSessionHasErrors('country_code');
    }

    public function test_profile_update_requires_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/update', ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------------------------
    // Password change
    // -------------------------------------------------------------------------

    public function test_password_can_be_changed(): void
    {
        $user = User::factory()->create(['password' => bcrypt('old-password')]);

        $this->actingAs($user)
            ->post('/profile/password', [
                'current_password'      => 'old-password',
                'password'              => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_password_change_rejected_when_current_password_wrong(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password')]);

        $this->actingAs($user)
            ->post('/profile/password', [
                'current_password'      => 'wrong-password',
                'password'              => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('current_password');

        // Password must not have changed
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('correct-password', $user->fresh()->password));
    }

    public function test_password_change_requires_min_8_chars(): void
    {
        $user = User::factory()->create(['password' => bcrypt('old-password')]);

        $this->actingAs($user)
            ->post('/profile/password', [
                'current_password'      => 'old-password',
                'password'              => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');
    }

    // -------------------------------------------------------------------------
    // Notification preferences
    // -------------------------------------------------------------------------

    public function test_notification_preferences_can_be_saved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/notifications', [
                'notify_quiz_results'  => '1',
                'notify_weekly_digest' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $prefs = $user->fresh()->notification_preferences;
        $this->assertTrue($prefs['notify_quiz_results']);
        $this->assertTrue($prefs['notify_weekly_digest']);
    }

    // -------------------------------------------------------------------------
    // Account deletion
    // -------------------------------------------------------------------------

    public function test_account_can_be_deleted_with_correct_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password')]);

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'correct-password'])
            ->assertRedirect('/');

        $this->assertGuest();
        // Soft-deleted, not hard-deleted
        $this->assertNotNull(User::onlyTrashed()->find($user->id));
    }

    public function test_account_deletion_rejected_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password')]);

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'wrong-password'])
            ->assertRedirect()
            ->assertSessionHasErrors('password');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull(User::find($user->id));
    }

    // -------------------------------------------------------------------------
    // HardDeleteExpiredUsersJob
    // -------------------------------------------------------------------------

    public function test_hard_delete_job_removes_users_deleted_more_than_30_days_ago(): void
    {
        $old = User::factory()->create();
        $old->delete();
        $old->forceFill(['deleted_at' => now()->subDays(31)])->save();

        $recent = User::factory()->create();
        $recent->delete();
        $recent->forceFill(['deleted_at' => now()->subDays(29)])->save();

        (new HardDeleteExpiredUsersJob())->handle();

        $this->assertNull(User::withTrashed()->find($old->id));
        $this->assertNotNull(User::onlyTrashed()->find($recent->id));
    }

    // -------------------------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------------------------

    public function test_dashboard_page_loads(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/my/dashboard')->assertOk();
    }

    public function test_dashboard_pass_rate_is_null_when_no_completed_attempts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/my/dashboard');

        $response->assertOk();
        $this->assertNull($response->viewData('passRate'));
    }

    public function test_dashboard_computes_pass_rate(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create();
        $enrollment = QuizEnrollment::factory()->create(['user_id' => $user->id, 'quiz_id' => $quiz->id]);

        Attempt::factory()->create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'completed', 'is_passed' => true,
        ]);
        Attempt::factory()->create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'completed', 'is_passed' => false,
        ]);

        $response = $this->actingAs($user)->get('/my/dashboard');

        $this->assertEquals(50, $response->viewData('passRate'));
    }

    // -------------------------------------------------------------------------
    // My Quizzes (regression: latest attempt must win keyBy)
    // -------------------------------------------------------------------------

    public function test_my_quizzes_page_loads(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/my/quizzes')->assertOk();
    }

    public function test_my_quizzes_latest_attempt_wins_not_oldest(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create();
        $enrollment = QuizEnrollment::factory()->create(['user_id' => $user->id, 'quiz_id' => $quiz->id]);

        // Older attempt: passed
        $older = Attempt::factory()->create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'completed', 'is_passed' => true,
            'started_at' => now()->subDays(2),
        ]);

        // Newer attempt: failed
        $newer = Attempt::factory()->create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'completed', 'is_passed' => false,
            'started_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get('/my/quizzes');

        $latestAttempts = $response->viewData('latestAttempts');
        $this->assertSame($newer->id, $latestAttempts[$quiz->id]->id);
    }

    // -------------------------------------------------------------------------
    // Attempts filter tabs
    // -------------------------------------------------------------------------

    public function test_my_attempts_page_loads(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/my/attempts')->assertOk();
    }

    public function test_my_attempts_filter_completed_excludes_in_progress(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create();
        $enrollment = QuizEnrollment::factory()->create(['user_id' => $user->id, 'quiz_id' => $quiz->id]);

        $completed = Attempt::factory()->create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id,
            'enrollment_id' => $enrollment->id, 'status' => 'completed',
        ]);
        $active = Attempt::factory()->create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id,
            'enrollment_id' => $enrollment->id, 'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get('/my/attempts?status=completed');

        $ids = $response->viewData('attempts')->pluck('id')->toArray();
        $this->assertContains($completed->id, $ids);
        $this->assertNotContains($active->id, $ids);
    }

    public function test_my_attempts_filter_missed_returns_abandoned_and_timed_out(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create();
        $enrollment = QuizEnrollment::factory()->create(['user_id' => $user->id, 'quiz_id' => $quiz->id]);

        $abandoned = Attempt::factory()->create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id,
            'enrollment_id' => $enrollment->id, 'status' => 'abandoned',
        ]);

        $response = $this->actingAs($user)->get('/my/attempts?status=missed');

        $ids = $response->viewData('attempts')->pluck('id')->toArray();
        $this->assertContains($abandoned->id, $ids);
    }

    // -------------------------------------------------------------------------
    // Certificates
    // -------------------------------------------------------------------------

    public function test_my_certificates_page_loads(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/my/certificates')->assertOk();
    }

    public function test_my_certificates_eager_loads_attempt_and_quiz(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create();
        $enrollment = QuizEnrollment::factory()->create(['user_id' => $user->id, 'quiz_id' => $quiz->id]);
        $attempt = Attempt::factory()->create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id,
            'enrollment_id' => $enrollment->id, 'status' => 'completed',
        ]);
        Certificate::factory()->create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id, 'attempt_id' => $attempt->id,
        ]);

        $response = $this->actingAs($user)->get('/my/certificates');

        $cert = $response->viewData('certificates')->first();
        $this->assertTrue($cert->relationLoaded('quiz'));
        $this->assertTrue($cert->relationLoaded('attempt'));
    }

    // -------------------------------------------------------------------------
    // Avatar upload
    // -------------------------------------------------------------------------

    public function test_avatar_upload_stores_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/avatar', ['avatar' => UploadedFile::fake()->image('photo.jpg')])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_avatar_upload_deletes_old_avatar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.jpg', 'dummy');

        $user = User::factory()->create(['avatar' => 'avatars/old.jpg']);

        $this->actingAs($user)
            ->post('/profile/avatar', ['avatar' => UploadedFile::fake()->image('new.jpg')])
            ->assertRedirect();

        Storage::disk('public')->assertMissing('avatars/old.jpg');
    }

    public function test_avatar_upload_rejects_non_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/avatar', ['avatar' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors('avatar');
    }

    // -------------------------------------------------------------------------
    // Default notification preferences
    // -------------------------------------------------------------------------

    public function test_new_user_has_default_notification_preferences(): void
    {
        $user = User::factory()->create();
        $prefs = $user->notification_preferences;

        $this->assertTrue($prefs['notify_quiz_results']);
        $this->assertFalse($prefs['notify_weekly_digest']);
    }
}
