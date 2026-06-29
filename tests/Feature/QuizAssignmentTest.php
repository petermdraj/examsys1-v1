<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\StudentBatch;
use App\Models\User;
use App\Services\Quiz\QuizAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuizAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;
    private User $student;
    private StudentBatch $batch;
    private Quiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'lecturer', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $this->batch = StudentBatch::create([
            'name'      => 'Test Batch',
            'code'      => 'TEST-1',
            'is_active' => true,
        ]);

        $this->lecturer = User::factory()->create(['role' => 'lecturer']);
        $this->lecturer->assignRole('lecturer');

        $this->student = User::factory()->create([
            'role'             => 'student',
            'student_batch_id' => $this->batch->id,
        ]);
        $this->student->assignRole('student');

        $this->quiz = Quiz::factory()->create([
            'lecturer_id' => $this->lecturer->id,
            'status'      => 'published',
        ]);

        $question = Question::create([
            'quiz_id'    => $this->quiz->id,
            'type'       => 'mcq_single',
            'content'    => 'Sample question?',
            'marks'      => 1,
            'sort_order' => 1,
        ]);
        QuestionOption::create([
            'question_id' => $question->id,
            'content'     => 'Yes',
            'is_correct'  => true,
            'sort_order'  => 1,
        ]);
        $this->quiz->update(['total_questions' => 1, 'total_marks' => 1]);
    }

    public function test_student_cannot_start_unassigned_quiz(): void
    {
        $this->actingAs($this->student)
            ->post(route('attempt.start', $this->quiz->slug))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_student_can_start_batch_assigned_quiz(): void
    {
        app(QuizAssignmentService::class)->assignToBatch($this->quiz, $this->lecturer, $this->batch);

        $this->actingAs($this->student)
            ->post(route('attempt.start', $this->quiz->slug))
            ->assertRedirect();
    }

    public function test_student_can_start_individually_assigned_quiz(): void
    {
        app(QuizAssignmentService::class)->assignToStudent($this->quiz, $this->lecturer, $this->student);

        $this->actingAs($this->student)
            ->post(route('attempt.start', $this->quiz->slug))
            ->assertRedirect();
    }

    public function test_public_registration_route_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }
}
