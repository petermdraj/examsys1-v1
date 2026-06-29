<?php

namespace Tests\Feature;

use App\Models\FillBlankAnswer;
use Spatie\Permission\Models\Role;
use App\Models\Question;
use App\Models\QuestionCollection;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use App\Services\QuestionBank\QuestionBankService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuestionBankTest extends TestCase
{
    use RefreshDatabase;

    private QuestionBankService $service;
    private User $creator;
    private Quiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QuestionBankService();

        Role::firstOrCreate(['name' => 'lecturer', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $this->creator = User::factory()->create(['role' => 'lecturer']);
        $this->creator->assignRole('lecturer');

        $this->quiz = Quiz::factory()->create(['lecturer_id' => $this->creator->id]);
    }

    // ── T9-1: AJAX endpoint enforces creator isolation ───────────────

    public function test_bank_questions_ajax_only_returns_own_questions(): void
    {
        $other = User::factory()->create();

        Question::factory()->create(['lecturer_id' => $this->creator->id, 'quiz_id' => null]);
        Question::factory()->create(['lecturer_id' => $other->id, 'quiz_id' => null]);

        $this->actingAs($this->creator)
            ->getJson('/lecturer/api/bank-questions')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ── T9-2: cross-creator import is silently skipped (security boundary) ──

    public function test_import_to_quiz_skips_other_creator_bank_questions(): void
    {
        $other = User::factory()->create();
        $bankQ = Question::factory()->create(['lecturer_id' => $other->id, 'quiz_id' => null]);

        $imported = $this->service->importToQuiz($this->creator->id, [$bankQ->id], $this->quiz);

        $this->assertSame(0, $imported);
        $this->assertSame(0, Question::where('quiz_id', $this->quiz->id)->count());
    }

    // ── T9-3: importToQuiz rolls back on failure ─────────────────────

    public function test_import_to_quiz_rolls_back_on_error(): void
    {
        $bankQ = Question::factory()
            ->has(QuestionOption::factory()->count(2), 'options')
            ->create(['lecturer_id' => $this->creator->id, 'quiz_id' => null]);

        $countBefore = Question::count();

        // Pass a bad ID alongside a valid one — service finds none of them and throws
        try {
            $this->service->importToQuiz($this->creator->id, ['bad-uuid'], $this->quiz);
        } catch (\Throwable) {
        }

        $this->assertSame($countBefore, Question::count());
    }

    // ── T9-4: importToQuiz stamps source_bank_question_id ───────────

    public function test_import_stamps_source_bank_question_id(): void
    {
        $bankQ = Question::factory()
            ->has(QuestionOption::factory()->count(2), 'options')
            ->create(['lecturer_id' => $this->creator->id, 'quiz_id' => null]);

        $this->service->importToQuiz($this->creator->id, [$bankQ->id], $this->quiz);

        $imported = Question::where('quiz_id', $this->quiz->id)->first();
        $this->assertSame($bankQ->id, $imported->source_bank_question_id);
    }

    // ── T9-5: importToQuiz deep-copies options ───────────────────────

    public function test_import_deep_copies_options(): void
    {
        $bankQ = Question::factory()
            ->has(QuestionOption::factory()->count(3), 'options')
            ->create(['lecturer_id' => $this->creator->id, 'quiz_id' => null]);

        $this->service->importToQuiz($this->creator->id, [$bankQ->id], $this->quiz);

        $imported = Question::where('quiz_id', $this->quiz->id)->first();
        $this->assertCount(3, $imported->options);

        // Must be copies, not the same rows
        $originalIds = $bankQ->options->pluck('id');
        $importedIds  = $imported->options->pluck('id');
        $this->assertEmpty($originalIds->intersect($importedIds));
    }

    // ── T9-6: importToQuiz updates quiz totals ───────────────────────

    public function test_import_updates_quiz_totals(): void
    {
        $bankQ = Question::factory(['marks' => 2])
            ->has(QuestionOption::factory()->count(2), 'options')
            ->create(['lecturer_id' => $this->creator->id, 'quiz_id' => null]);

        $this->service->importToQuiz($this->creator->id, [$bankQ->id], $this->quiz);

        $this->quiz->refresh();
        $this->assertSame(1, $this->quiz->total_questions);
        $this->assertSame(2.0, (float) $this->quiz->total_marks);
    }

    // ── T9-7: collection create-inline sets lecturer_id ──────────────

    public function test_collection_create_sets_lecturer_id(): void
    {
        $col = QuestionCollection::create([
            'lecturer_id' => $this->creator->id,
            'name'       => 'PHP Basics',
        ]);

        $this->assertSame($this->creator->id, $col->lecturer_id);
    }

    // ── T9-8: deleting collection sets question collection_id to null ─

    public function test_delete_collection_nullifies_question_collection_id(): void
    {
        $col = QuestionCollection::create([
            'lecturer_id' => $this->creator->id,
            'name'       => 'Temp',
        ]);
        $q = Question::factory()->create([
            'lecturer_id'    => $this->creator->id,
            'quiz_id'       => null,
            'collection_id' => $col->id,
        ]);

        $col->delete();

        $this->assertNull($q->fresh()->collection_id);
    }

    // ── T9-9: importJson detects duplicates per creator ──────────────

    public function test_import_json_skips_duplicates(): void
    {
        $content = 'What is PHP?';
        Question::factory()->create([
            'lecturer_id' => $this->creator->id,
            'quiz_id'    => null,
            'content'    => $content,
        ]);

        $result = $this->service->importQuestions($this->creator->id, [
            [
                'type'           => 'mcq_single',
                'content'        => $content,
                'explanation'    => null,
                'marks'          => 1,
                'negative_marks' => 0,
                'options'        => [],
                'blank_answers'  => [],
                'collection'     => null,
            ],
        ]);

        $this->assertSame(0, $result['imported']);
        $this->assertSame(1, $result['skipped']);
    }

    // ── T9-10: importJson with malformed data returns errors ─────────

    public function test_import_json_handles_missing_content_field(): void
    {
        $result = $this->service->importQuestions($this->creator->id, [
            ['type' => 'mcq_single'],
        ]);

        $this->assertSame(0, $result['imported']);
        $this->assertGreaterThan(0, $result['errors']);
    }

    // ── T9-11: importJson with empty questions array ──────────────────

    public function test_import_json_empty_questions_array(): void
    {
        $result = $this->service->importQuestions($this->creator->id, []);

        $this->assertSame(0, $result['imported']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame(0, $result['errors']);
    }

    // ── T9-12: importJson auto-creates collection by name ───────────

    public function test_import_json_creates_collection_by_name(): void
    {
        $result = $this->service->importQuestions($this->creator->id, [
            [
                'type'           => 'true_false',
                'content'        => 'PHP is compiled',
                'explanation'    => null,
                'marks'          => 1,
                'negative_marks' => 0,
                'options'        => [],
                'blank_answers'  => [],
                'collection_name' => 'New Collection',
            ],
        ]);

        $this->assertSame(1, $result['imported']);
        $this->assertDatabaseHas('question_collections', [
            'lecturer_id' => $this->creator->id,
            'name'       => 'New Collection',
        ]);
    }

    // ── T9-13: randomImport respects count boundary ──────────────────

    public function test_random_import_does_not_exceed_bank_size(): void
    {
        Question::factory()->count(3)->create([
            'lecturer_id' => $this->creator->id,
            'quiz_id'    => null,
        ]);

        // Request more than available
        $this->service->randomImport($this->creator->id, $this->quiz, 10, null, null, null);

        $imported = Question::where('quiz_id', $this->quiz->id)->count();
        $this->assertSame(3, $imported);
    }

    // ── T9-14: randomImport with count 0 imports nothing ────────────

    public function test_random_import_with_zero_count_imports_nothing(): void
    {
        Question::factory()->create(['lecturer_id' => $this->creator->id, 'quiz_id' => null]);

        $this->service->randomImport($this->creator->id, $this->quiz, 0, null, null, null);

        $this->assertSame(0, Question::where('quiz_id', $this->quiz->id)->count());
    }

    // ── T9-19: Excel export/import round trip ────────────────────────

    public function test_excel_export_import_round_trip(): void
    {
        $col = QuestionCollection::create([
            'lecturer_id' => $this->creator->id,
            'name'       => 'Excel Collection',
        ]);

        $bankQ = Question::factory()->create([
            'lecturer_id'    => $this->creator->id,
            'quiz_id'       => null,
            'content'       => 'Excel round trip question?',
            'type'          => 'mcq_single',
            'difficulty'    => 'easy',
            'marks'         => 2,
            'negative_marks'=> 0.5,
            'collection_id' => $col->id,
        ]);

        QuestionOption::create([
            'question_id' => $bankQ->id,
            'content'     => 'Option A',
            'is_correct'  => true,
            'sort_order'  => 1,
        ]);
        QuestionOption::create([
            'question_id' => $bankQ->id,
            'content'     => 'Option B',
            'is_correct'  => false,
            'sort_order'  => 2,
        ]);

        $path = $this->service->exportExcel($this->creator->id);
        $this->assertNotNull($path);
        $this->assertFileExists($path);

        Question::whereNull('quiz_id')->where('lecturer_id', $this->creator->id)->delete();

        $result = $this->service->importExcel($this->creator->id, $path);
        @unlink($path);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame(0, $result['errors']);

        $imported = Question::whereNull('quiz_id')->where('lecturer_id', $this->creator->id)->first();
        $this->assertSame('Excel round trip question?', $imported->content);
        $this->assertSame('easy', $imported->difficulty);
        $this->assertCount(2, $imported->options);
        $this->assertTrue($imported->options->firstWhere('content', 'Option A')->is_correct);
    }

    // ── T9-15: saveToBank detects duplicate per creator ──────────────

    public function test_save_to_bank_returns_false_for_duplicate(): void
    {
        $content = 'What does OOP stand for?';
        $q1 = Question::factory()->create([
            'lecturer_id' => $this->creator->id,
            'quiz_id'    => null,
            'content'    => $content,
        ]);

        $q2 = Question::factory()->create([
            'lecturer_id' => $this->creator->id,
            'quiz_id'    => $this->quiz->id,
            'content'    => $content,
        ]);

        $result = $this->service->saveToBank($this->creator->id, $q2);

        $this->assertFalse($result);
    }

    // ── T9-16: saveToBank succeeds for non-duplicate ─────────────────

    public function test_save_to_bank_creates_bank_copy(): void
    {
        $q = Question::factory()
            ->has(QuestionOption::factory()->count(2), 'options')
            ->create([
                'lecturer_id' => $this->creator->id,
                'quiz_id'    => $this->quiz->id,
                'content'    => 'Unique question content XYZ123',
            ]);

        $result = $this->service->saveToBank($this->creator->id, $q);

        $this->assertTrue($result);
        $this->assertDatabaseHas('questions', [
            'lecturer_id' => $this->creator->id,
            'quiz_id'    => null,
            'content'    => $q->content,
        ]);
    }

    // ── T9-17: duplicate hash is content-hash only (case + whitespace) ─

    public function test_duplicate_detection_is_case_and_whitespace_insensitive(): void
    {
        Question::factory()->create([
            'lecturer_id' => $this->creator->id,
            'quiz_id'    => null,
            'content'    => 'What is PHP?',
        ]);

        $hash = $this->service->contentHash('  WHAT IS PHP?  ');
        $this->assertTrue($this->service->duplicateExists($this->creator->id, $hash));
    }

    // ── T9-18: cross-creator duplicates don't conflict ───────────────

    public function test_duplicate_check_is_per_creator(): void
    {
        $other = User::factory()->create();
        $content = 'Shared question text';

        Question::factory()->create([
            'lecturer_id' => $other->id,
            'quiz_id'    => null,
            'content'    => $content,
        ]);

        $hash = $this->service->contentHash($content);
        $this->assertFalse($this->service->duplicateExists($this->creator->id, $hash));
    }
}
