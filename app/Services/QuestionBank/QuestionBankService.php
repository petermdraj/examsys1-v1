<?php

namespace App\Services\QuestionBank;

use App\Models\Category;
use App\Models\FillBlankAnswer;
use App\Models\Question;
use App\Models\QuestionCollection;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Activitylog\Facades\Activity;

class QuestionBankService
{
    private const EXCEL_OPTION_COUNT = 6;

    /** @var list<string> */
    private const EXCEL_HEADERS = [
        'type',
        'content',
        'difficulty',
        'marks',
        'negative_marks',
        'explanation',
        'hint',
        'collection_name',
        'subject',
        'option_1',
        'correct_1',
        'option_2',
        'correct_2',
        'option_3',
        'correct_3',
        'option_4',
        'correct_4',
        'option_5',
        'correct_5',
        'option_6',
        'correct_6',
        'blank_answers',
    ];
    /**
     * Deep-copy bank questions into a quiz.
     * Runs in a transaction — all-or-nothing.
     */
    public function importToQuiz(string $creatorId, array $bankQuestionIds, Quiz $quiz, ?User $creator = null): int
    {
        return DB::transaction(function () use ($creatorId, $bankQuestionIds, $quiz) {
            $maxSort = $quiz->questions()->max('sort_order') ?? 0;
            $imported = 0;

            foreach ($bankQuestionIds as $bankId) {
                $src = Question::whereNull('quiz_id')
                    ->where('lecturer_id', $creatorId)
                    ->with(['options', 'fillBlankAnswers'])
                    ->find($bankId);

                if (!$src) continue;

                $newQ = Question::create([
                    'quiz_id'                => $quiz->id,
                    'lecturer_id'             => $creatorId,
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
                ->withProperties(['lecturer_id' => $creatorId, 'quiz_id' => $quiz->id, 'count' => $imported])
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
            'lecturer_id'    => $creatorId,
            'type'          => $question->type,
            'content'       => $question->content,
            'explanation'   => $question->explanation,
            'marks'         => $question->marks,
            'negative_marks' => $question->negative_marks,
            'hint'          => $question->hint,
            'difficulty'    => $question->difficulty,
            'collection_id' => $collectionId,
            'category_id'   => $question->category_id ?? $question->quiz?->category_id,
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
        ?string $type = null,
        ?string $categoryId = null
    ): int {
        $ids = Question::whereNull('quiz_id')
            ->where('lecturer_id', $creatorId)
            ->when($collectionId, fn($q) => $q->where('collection_id', $collectionId))
            ->when($categoryId,   fn($q) => $q->where('category_id', $categoryId))
            ->when($difficulty,   fn($q) => $q->where('difficulty', $difficulty))
            ->when($type,         fn($q) => $q->where('type', $type))
            ->inRandomOrder()
            ->limit($count)
            ->pluck('id')
            ->all();

        return $this->importToQuiz($creatorId, $ids, $quiz);
    }

    /**
     * Export all bank questions to an .xlsx file.
     * Returns the temp file path, or null when the bank is empty.
     */
    public function exportExcel(string $creatorId): ?string
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->fromArray([self::EXCEL_HEADERS], null, 'A1');

        $row = 2;
        $count = 0;

        Question::whereNull('quiz_id')
            ->where('lecturer_id', $creatorId)
            ->with(['options', 'fillBlankAnswers', 'collection', 'category'])
            ->orderBy('created_at')
            ->chunk(100, function ($chunk) use ($sheet, &$row, &$count) {
                foreach ($chunk as $q) {
                    $options = $q->options->values();
                    $line    = [
                        $q->type,
                        $q->content,
                        $q->difficulty ?? '',
                        (float) $q->marks,
                        (float) $q->negative_marks,
                        $q->explanation ?? '',
                        $q->hint ?? '',
                        $q->collection?->name ?? '',
                        $q->category?->name ?? '',
                    ];

                    for ($i = 0; $i < self::EXCEL_OPTION_COUNT; $i++) {
                        $opt = $options->get($i);
                        $line[] = $opt?->content ?? '';
                        $line[] = $opt ? ($opt->is_correct ? 'yes' : 'no') : '';
                    }

                    $line[] = $q->fillBlankAnswers->pluck('answer')->implode('|');

                    $sheet->fromArray([$line], null, 'A' . $row);
                    $row++;
                    $count++;
                }
            });

        if ($count === 0) {
            return null;
        }

        activity()
            ->causedByAnonymous()
            ->withProperties(['lecturer_id' => $creatorId, 'count' => $count])
            ->log('bank_export_generated');

        $path = tempnam(sys_get_temp_dir(), 'qbank_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    /**
     * Generate a sample Excel template with headers and example rows.
     */
    public function sampleExcel(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->fromArray([self::EXCEL_HEADERS], null, 'A1');

        $examples = [
            [
                'mcq_single',
                'What is 2 + 2?',
                'easy',
                1,
                0,
                'Basic arithmetic.',
                '',
                'Math Basics',
                'Mathematics',
                '3', 'no',
                '4', 'yes',
                '5', 'no',
                '6', 'no',
                '', '',
                '', '',
                '', '',
                '',
            ],
            [
                'true_false',
                'PHP is a compiled language.',
                'medium',
                1,
                0.25,
                'PHP is interpreted, not compiled.',
                '',
                'PHP Snippets',
                'Programming',
                'True', 'no',
                'False', 'yes',
                '', '',
                '', '',
                '', '',
                '', '',
                '',
            ],
            [
                'fill_blank',
                'The capital of France is ____.',
                'easy',
                1,
                0,
                'Paris is the capital and largest city of France.',
                '',
                'Europe Capitals',
                'Geography',
                '', '',
                '', '',
                '', '',
                '', '',
                '', '',
                '', '',
                '', '',
                'Paris',
            ],
        ];

        $row = 2;
        foreach ($examples as $line) {
            $sheet->fromArray([$line], null, 'A' . $row);
            $row++;
        }

        $path = tempnam(sys_get_temp_dir(), 'qbank_sample_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    /**
     * Import questions from an Excel file (.xlsx / .xls).
     * Returns ['imported' => N, 'skipped' => N, 'errors' => N, 'total' => N].
     */
    public function importExcel(string $creatorId, string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => 0, 'total' => 0];
        }

        $headers = array_map(
            fn ($h) => strtolower(trim((string) $h)),
            $rows[0]
        );

        $questions = [];
        foreach (array_slice($rows, 1) as $row) {
            $assoc = [];
            foreach ($headers as $i => $header) {
                if ($header === '') {
                    continue;
                }
                $assoc[$header] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }

            if ($this->excelRowIsEmpty($assoc)) {
                continue;
            }

            $questions[] = $this->normalizeExcelRow($assoc);
        }

        $result         = $this->importQuestions($creatorId, $questions);
        $result['total'] = count($questions);

        return $result;
    }

    /**
     * Import questions from a normalized array.
     * Returns ['imported' => N, 'skipped' => N, 'errors' => N].
     */
    public function importQuestions(string $creatorId, array $questions): array
    {
        $imported = $skipped = $errors = 0;

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
            if (! empty($row['collection_name'])) {
                $collectionName = trim($row['collection_name']);
                $col = QuestionCollection::where('lecturer_id', $creatorId)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($collectionName)])
                    ->first()
                    ?? QuestionCollection::create(['lecturer_id' => $creatorId, 'name' => $collectionName]);
                $collectionId = $col->id;
            }

            $categoryId = $this->resolveCategoryId(
                $row['subject'] ?? $row['category_name'] ?? $row['category'] ?? null
            );

            $q = Question::create([
                'quiz_id'        => null,
                'lecturer_id'     => $creatorId,
                'type'           => $row['type'],
                'content'        => $content,
                'explanation'    => isset($row['explanation']) ? strip_tags($row['explanation']) : null,
                'marks'          => (float) ($row['marks'] ?? 1),
                'negative_marks' => (float) ($row['negative_marks'] ?? 0),
                'hint'           => isset($row['hint']) ? strip_tags($row['hint']) : null,
                'difficulty'     => in_array($row['difficulty'] ?? '', ['easy', 'medium', 'hard'], true)
                    ? $row['difficulty'] : null,
                'collection_id'  => $collectionId,
                'category_id'    => $categoryId,
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
            ->withProperties(['lecturer_id' => $creatorId, 'imported' => $imported, 'skipped' => $skipped, 'errors' => $errors])
            ->log('bank_import_completed');

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * @param  array<string, string>  $row
     */
    private function normalizeExcelRow(array $row): array
    {
        $options = [];
        for ($i = 1; $i <= self::EXCEL_OPTION_COUNT; $i++) {
            $content = $row["option_{$i}"] ?? '';
            if ($content === '') {
                continue;
            }
            $options[] = [
                'content'    => $content,
                'is_correct' => $this->parseExcelBoolean($row["correct_{$i}"] ?? ''),
            ];
        }

        $blankAnswers = [];
        if (! empty($row['blank_answers'])) {
            $blankAnswers = array_values(array_filter(array_map(
                'trim',
                preg_split('/[|;]/', $row['blank_answers']) ?: []
            )));
        }

        return [
            'type'            => $row['type'] ?? '',
            'content'         => $row['content'] ?? ($row['question'] ?? ''),
            'difficulty'      => $row['difficulty'] ?? '',
            'marks'           => is_numeric($row['marks'] ?? null) ? (float) $row['marks'] : 1,
            'negative_marks'  => is_numeric($row['negative_marks'] ?? null) ? (float) $row['negative_marks'] : 0,
            'explanation'     => $row['explanation'] ?? '',
            'hint'            => $row['hint'] ?? '',
            'collection_name' => $row['collection_name'] ?? ($row['collection'] ?? ''),
            'subject'         => $row['subject'] ?? ($row['category_name'] ?? ($row['category'] ?? '')),
            'options'         => $options,
            'blank_answers'   => $blankAnswers,
        ];
    }

    /**
     * @param  array<string, string>  $row
     */
    private function excelRowIsEmpty(array $row): bool
    {
        $content = trim($row['content'] ?? ($row['question'] ?? ''));

        return $content === '';
    }

    private function parseExcelBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'yes', 'y', 'true', 'correct'], true);
    }

    private function resolveCategoryId(?string $name): ?string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        return Category::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->value('id');
    }

    /** @deprecated Use importQuestions() or importExcel() */
    public function importJson(string $creatorId, array $data): array
    {
        return $this->importQuestions($creatorId, $data['questions'] ?? []);
    }

    public function contentHash(string $content): string
    {
        return md5(strtolower(trim(strip_tags($content))));
    }

    public function duplicateExists(string $creatorId, string $hash): bool
    {
        // Compute hash in PHP; load content and compare to avoid SQL dialect issues (MySQL MD5 vs SQLite).
        return Question::whereNull('quiz_id')
            ->where('lecturer_id', $creatorId)
            ->get(['content'])
            ->contains(fn($q) => $this->contentHash($q->content) === $hash);
    }
}
