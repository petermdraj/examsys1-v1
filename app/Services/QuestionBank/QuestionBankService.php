<?php

namespace App\Services\QuestionBank;

use App\Exceptions\PlanLimitExceededException;
use App\Models\FillBlankAnswer;
use App\Models\Question;
use App\Models\QuestionCollection;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use App\Services\Quiz\PlanLimitService;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class QuestionBankService
{
    /**
     * Deep-copy bank questions into a quiz.
     * Runs in a transaction — all-or-nothing.
     */
    public function importToQuiz(string $creatorId, array $bankQuestionIds, Quiz $quiz, ?User $creator = null): int
    {
        $limitService = app(PlanLimitService::class);
        $creatorUser  = $creator ?? \App\Models\User::find($creatorId);

        return DB::transaction(function () use ($creatorId, $bankQuestionIds, $quiz, $limitService, $creatorUser) {
            $maxSort = $quiz->questions()->max('sort_order') ?? 0;
            $imported = 0;

            foreach ($bankQuestionIds as $bankId) {
                // Enforce per-quiz limit before each import
                if ($creatorUser) {
                    try {
                        $limitService->assertCanAddQuestion($creatorUser, $quiz->id);
                    } catch (PlanLimitExceededException $e) {
                        // Stop importing; partial import up to limit is allowed
                        break;
                    }
                }

                $src = Question::whereNull('quiz_id')
                    ->where('creator_id', $creatorId)
                    ->with(['options', 'fillBlankAnswers'])
                    ->find($bankId);

                if (!$src) continue;

                $newQ = Question::create([
                    'quiz_id'                => $quiz->id,
                    'creator_id'             => $creatorId,
                    'type'                   => $src->type,
                    'content'                => $src->content,
                    'explanation'            => $src->explanation,
                    'marks'                  => $src->marks,
                    'negative_marks'         => $src->negative_marks,
                    'hint'                   => $src->hint,
                    'difficulty'             => $src->difficulty,
                    'sort_order'             => ++$maxSort,
                    'source_bank_question_id' => $src->id,
                ]);

                foreach ($src->options as $i => $opt) {
                    QuestionOption::create([
                        'question_id' => $newQ->id,
                        'content'     => $opt->content,
                        'is_correct'  => $opt->is_correct,
                        'sort_order'  => $i + 1,
                    ]);
                }

                foreach ($src->fillBlankAnswers as $ans) {
                    FillBlankAnswer::create(['question_id' => $newQ->id, 'answer' => $ans->answer]);
                }

                $imported++;
            }

            $quiz->update([
                'total_questions' => $quiz->questions()->count(),
                'total_marks'     => $quiz->questions()->sum('marks'),
            ]);

            activity()
                ->causedByAnonymous()
                ->withProperties(['creator_id' => $creatorId, 'quiz_id' => $quiz->id, 'count' => $imported])
                ->log('bank_question_imported');

            return $imported;
        });
    }

    /**
     * Save a quiz question as a bank question copy.
     * Returns false if a duplicate already exists in this creator's bank.
     */
    public function saveToBank(string $creatorId, Question $question, ?string $collectionId = null): bool
    {
        $hash = $this->contentHash($question->content);

        if ($this->duplicateExists($creatorId, $hash)) {
            return false;
        }

        $bankQ = Question::create([
            'quiz_id'       => null,
            'creator_id'    => $creatorId,
            'type'          => $question->type,
            'content'       => $question->content,
            'explanation'   => $question->explanation,
            'marks'         => $question->marks,
            'negative_marks' => $question->negative_marks,
            'hint'          => $question->hint,
            'difficulty'    => $question->difficulty,
            'collection_id' => $collectionId,
            'sort_order'    => 0,
        ]);

        foreach ($question->options()->orderBy('sort_order')->get() as $i => $opt) {
            QuestionOption::create([
                'question_id' => $bankQ->id,
                'content'     => $opt->content,
                'is_correct'  => $opt->is_correct,
                'sort_order'  => $i + 1,
            ]);
        }

        foreach ($question->fillBlankAnswers as $ans) {
            FillBlankAnswer::create(['question_id' => $bankQ->id, 'answer' => $ans->answer]);
        }

        return true;
    }

    /**
     * Pull N random bank questions matching filters into a quiz.
     * If fewer than $count exist, imports all available.
     */
    public function randomImport(
        string $creatorId,
        Quiz $quiz,
        int $count,
        ?string $collectionId = null,
        ?string $difficulty = null,
        ?string $type = null
    ): int {
        $ids = Question::whereNull('quiz_id')
            ->where('creator_id', $creatorId)
            ->when($collectionId, fn($q) => $q->where('collection_id', $collectionId))
            ->when($difficulty,   fn($q) => $q->where('difficulty', $difficulty))
            ->when($type,         fn($q) => $q->where('type', $type))
            ->inRandomOrder()
            ->limit($count)
            ->pluck('id')
            ->all();

        return $this->importToQuiz($creatorId, $ids, $quiz);
    }

    /**
     * Stream-export all bank questions as a JSON download.
     * Uses chunking to avoid OOM on large banks.
     */
    public function exportJson(string $creatorId): array
    {
        $questions = [];

        Question::whereNull('quiz_id')
            ->where('creator_id', $creatorId)
            ->with(['options', 'fillBlankAnswers', 'collection'])
            ->chunk(100, function ($chunk) use (&$questions) {
                foreach ($chunk as $q) {
                    $questions[] = [
                        'content'         => $q->content,
                        'type'            => $q->type,
                        'difficulty'      => $q->difficulty,
                        'marks'           => (float) $q->marks,
                        'negative_marks'  => (float) $q->negative_marks,
                        'explanation'     => $q->explanation,
                        'hint'            => $q->hint,
                        'collection_name' => $q->collection?->name,
                        'options'         => $q->options->map(fn($o) => [
                            'content'    => $o->content,
                            'is_correct' => $o->is_correct,
                        ])->all(),
                        'blank_answers'   => $q->fillBlankAnswers->pluck('answer')->all(),
                    ];
                }
            });

        activity()
            ->causedByAnonymous()
            ->withProperties(['creator_id' => $creatorId, 'count' => count($questions)])
            ->log('bank_export_generated');

        return ['version' => 1, 'questions' => $questions];
    }

    /**
     * Import questions from a validated JSON file.
     * Returns ['imported' => N, 'skipped' => N, 'errors' => N].
     */
    public function importJson(string $creatorId, array $data): array
    {
        $imported = $skipped = $errors = 0;
        $questions = $data['questions'] ?? [];

        foreach ($questions as $row) {
            if (empty($row['content']) || empty($row['type'])) {
                $errors++;
                continue;
            }

            $content = strip_tags($row['content']);
            $hash    = $this->contentHash($content);

            if ($this->duplicateExists($creatorId, $hash)) {
                $skipped++;
                continue;
            }

            $collectionId = null;
            if (!empty($row['collection_name'])) {
                $collectionName = trim($row['collection_name']);
                $col = QuestionCollection::where('creator_id', $creatorId)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($collectionName)])
                    ->first()
                    ?? QuestionCollection::create(['creator_id' => $creatorId, 'name' => $collectionName]);
                $collectionId = $col->id;
            }

            $q = Question::create([
                'quiz_id'        => null,
                'creator_id'     => $creatorId,
                'type'           => $row['type'],
                'content'        => $content,
                'explanation'    => isset($row['explanation']) ? strip_tags($row['explanation']) : null,
                'marks'          => (float) ($row['marks'] ?? 1),
                'negative_marks' => (float) ($row['negative_marks'] ?? 0),
                'hint'           => isset($row['hint']) ? strip_tags($row['hint']) : null,
                'difficulty'     => in_array($row['difficulty'] ?? '', ['easy', 'medium', 'hard'])
                    ? $row['difficulty'] : null,
                'collection_id'  => $collectionId,
                'sort_order'     => 0,
            ]);

            foreach ($row['options'] ?? [] as $i => $opt) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'content'     => strip_tags($opt['content'] ?? ''),
                    'is_correct'  => (bool) ($opt['is_correct'] ?? false),
                    'sort_order'  => $i + 1,
                ]);
            }

            foreach ($row['blank_answers'] ?? [] as $ans) {
                FillBlankAnswer::create(['question_id' => $q->id, 'answer' => $ans]);
            }

            $imported++;
        }

        activity()
            ->causedByAnonymous()
            ->withProperties(['creator_id' => $creatorId, 'imported' => $imported, 'skipped' => $skipped, 'errors' => $errors])
            ->log('bank_import_completed');

        return compact('imported', 'skipped', 'errors');
    }

    public function contentHash(string $content): string
    {
        return md5(strtolower(trim(strip_tags($content))));
    }

    public function duplicateExists(string $creatorId, string $hash): bool
    {
        // Compute hash in PHP; load content and compare to avoid SQL dialect issues (MySQL MD5 vs SQLite).
        return Question::whereNull('quiz_id')
            ->where('creator_id', $creatorId)
            ->get(['content'])
            ->contains(fn($q) => $this->contentHash($q->content) === $hash);
    }
}
