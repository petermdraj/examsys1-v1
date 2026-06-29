<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\GradeReports;
use App\Filament\Lecturer\Pages\Reports;
use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\StudentBatch;
use App\Models\User;
use App\Services\Exam\ExamGradeReportService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExamGradeReportTest extends TestCase
{
    use RefreshDatabase;

    private ExamGradeReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->service = app(ExamGradeReportService::class);
    }

    public function test_best_attempt_mode_returns_highest_score_per_student(): void
    {
        ['quiz' => $quiz, 'student' => $student] = $this->makeQuizWithStudent();

        $this->makeCompletedAttempt($quiz, $student, [
            'attempt_number' => 1,
            'percentage'     => 55,
            'submitted_at'   => now()->subDay(),
        ]);
        $enrollment = QuizEnrollment::where('quiz_id', $quiz->id)->where('user_id', $student->id)->first();
        $this->makeCompletedAttempt($quiz, $student, [
            'attempt_number' => 2,
            'percentage'     => 88,
            'submitted_at'   => now(),
        ], $enrollment);

        $rows = $this->service->buildRows($quiz, null, 'best');

        $this->assertCount(1, $rows);
        $this->assertEquals(88, (float) $rows->first()['percentage']);
        $this->assertEquals(2, $rows->first()['attempt_number']);
    }

    public function test_latest_attempt_mode_returns_most_recent_attempt(): void
    {
        ['quiz' => $quiz, 'student' => $student] = $this->makeQuizWithStudent();

        $this->makeCompletedAttempt($quiz, $student, [
            'attempt_number' => 1,
            'percentage'     => 88,
            'submitted_at'   => now()->subDay(),
        ]);
        $enrollment = QuizEnrollment::where('quiz_id', $quiz->id)->where('user_id', $student->id)->first();
        $this->makeCompletedAttempt($quiz, $student, [
            'attempt_number' => 2,
            'percentage'     => 55,
            'submitted_at'   => now(),
        ], $enrollment);

        $rows = $this->service->buildRows($quiz, null, 'latest');

        $this->assertCount(1, $rows);
        $this->assertEquals(55, (float) $rows->first()['percentage']);
        $this->assertEquals(2, $rows->first()['attempt_number']);
    }

    public function test_all_attempt_mode_returns_every_attempt(): void
    {
        ['quiz' => $quiz, 'student' => $student] = $this->makeQuizWithStudent();

        $this->makeCompletedAttempt($quiz, $student, ['attempt_number' => 1, 'percentage' => 55]);
        $enrollment = QuizEnrollment::where('quiz_id', $quiz->id)->where('user_id', $student->id)->first();
        $this->makeCompletedAttempt($quiz, $student, ['attempt_number' => 2, 'percentage' => 88], $enrollment);

        $rows = $this->service->buildRows($quiz, null, 'all');

        $this->assertCount(2, $rows);
    }

    public function test_batch_filter_excludes_other_batches(): void
    {
        ['quiz' => $quiz] = $this->makeQuizWithStudent();

        $batchA = StudentBatch::create(['name' => 'Batch A', 'code' => 'A1', 'is_active' => true]);
        $batchB = StudentBatch::create(['name' => 'Batch B', 'code' => 'B1', 'is_active' => true]);

        $studentA = User::factory()->create(['role' => 'student', 'student_batch_id' => $batchA->id]);
        $studentB = User::factory()->create(['role' => 'student', 'student_batch_id' => $batchB->id]);

        $this->makeCompletedAttempt($quiz, $studentA, ['percentage' => 70]);
        $this->makeCompletedAttempt($quiz, $studentB, ['percentage' => 80]);

        $rows = $this->service->buildRows($quiz, $batchA->id, 'best');

        $this->assertCount(1, $rows);
        $this->assertEquals('Batch A', $rows->first()['batch']);
    }

    public function test_excel_export_returns_xlsx_file_with_data(): void
    {
        ['quiz' => $quiz, 'student' => $student] = $this->makeQuizWithStudent();
        $this->makeCompletedAttempt($quiz, $student, ['percentage' => 75, 'is_passed' => true]);

        $path = $this->service->exportExcel($quiz, null, 'best');

        $this->assertNotNull($path);
        $this->assertFileExists($path);

        $sheet = IOFactory::load($path)->getActiveSheet();
        $this->assertEquals($quiz->title, $sheet->getCell('B1')->getValue());
        $this->assertEquals($student->name, $sheet->getCell('B7')->getValue());

        @unlink($path);
    }

    public function test_pdf_export_returns_pdf_response(): void
    {
        ['quiz' => $quiz, 'student' => $student] = $this->makeQuizWithStudent();
        $this->makeCompletedAttempt($quiz, $student, ['percentage' => 75, 'is_passed' => true]);

        $response = $this->service->exportPdf($quiz, null, 'best');

        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_lecturer_cannot_export_another_lecturers_quiz(): void
    {
        $owner    = User::factory()->create(['role' => 'lecturer']);
        $intruder = User::factory()->create(['role' => 'lecturer']);
        $owner->assignRole('lecturer');
        $intruder->assignRole('lecturer');

        $quiz = Quiz::factory()->create(['lecturer_id' => $owner->id]);

        Livewire::actingAs($intruder)
            ->test(Reports::class)
            ->set('selectedQuizId', $quiz->id)
            ->call('exportExcel')
            ->assertStatus(403);
    }

    public function test_admin_can_access_grade_reports_page(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        $this->assertTrue(GradeReports::canAccess());
    }

    public function test_admin_can_export_any_quiz_via_livewire(): void
    {
        $admin    = User::factory()->create(['role' => 'super_admin']);
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $admin->assignRole('super_admin');

        $quiz    = Quiz::factory()->create(['lecturer_id' => $lecturer->id]);
        $student = User::factory()->create(['role' => 'student']);
        $this->makeCompletedAttempt($quiz, $student, ['percentage' => 82, 'is_passed' => true]);

        Livewire::actingAs($admin)
            ->test(GradeReports::class)
            ->fillForm([
                'quiz_id'      => $quiz->id,
                'batch_id'     => null,
                'attempt_mode' => 'best',
            ])
            ->call('exportExcel')
            ->assertHasNoErrors();
    }

    private function makeQuizWithStudent(): array
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $student  = User::factory()->create(['role' => 'student']);
        $quiz     = Quiz::factory()->create(['lecturer_id' => $lecturer->id]);

        return compact('lecturer', 'student', 'quiz');
    }

    private function makeCompletedAttempt(Quiz $quiz, User $student, array $overrides = [], ?QuizEnrollment $enrollment = null): Attempt
    {
        $enrollment ??= QuizEnrollment::factory()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
        ]);

        return Attempt::factory()->create(array_merge([
            'quiz_id'       => $quiz->id,
            'user_id'       => $student->id,
            'enrollment_id' => $enrollment->id,
            'status'        => 'completed',
            'submitted_at'  => now(),
            'score'         => 7,
            'total_marks'   => 10,
            'percentage'    => 70,
            'is_passed'     => true,
            'attempt_number'=> 1,
        ], $overrides));
    }
}
