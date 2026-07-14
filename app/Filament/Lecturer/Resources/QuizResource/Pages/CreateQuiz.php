<?php

namespace App\Filament\Lecturer\Resources\QuizResource\Pages;

use App\Filament\Lecturer\Resources\QuizResource;
use App\Models\FillBlankAnswer;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Services\QuestionBank\QuestionBankService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['lecturer_id'] = auth()->id();
        QuizResource::applyResultsReleaseMode($data);

        return $data;
    }

    /**
     * Persist a draft as soon as Basic Info + Settings are done so the Questions
     * step can attach bank imports / AI items (those APIs need a quiz_id).
     */
    public function saveDraftFromWizard(): void
    {
        if ($this->getRecord()) {
            return;
        }

        $raw = $this->form->getRawState();

        $cover = $raw['cover_image'] ?? null;
        if (is_array($cover)) {
            $cover = collect($cover)->filter()->first();
        }

        $data = [
            'title'                     => $raw['title'] ?? null,
            'slug'                      => $raw['slug'] ?? null,
            'category_id'               => $raw['category_id'] ?? null,
            'description'               => $raw['description'] ?? null,
            'cover_image'               => $cover,
            'duration_minutes'          => $raw['duration_minutes'] ?? null,
            'max_attempts'              => $raw['max_attempts'] ?? null,
            'pass_percentage'           => $raw['pass_percentage'] ?? 60,
            'visibility'                => $raw['visibility'] ?? 'public',
            'shuffle_questions'         => (bool) ($raw['shuffle_questions'] ?? false),
            'shuffle_options'           => (bool) ($raw['shuffle_options'] ?? false),
            'results_release_mode'      => $raw['results_release_mode'] ?? 'immediate',
            'allow_review_after_submit' => (bool) ($raw['allow_review_after_submit'] ?? true),
            'negative_marking_enabled'  => (bool) ($raw['negative_marking_enabled'] ?? false),
            'certificate_enabled'       => (bool) ($raw['certificate_enabled'] ?? false),
            'certificate_template'      => $raw['certificate_template'] ?? 'classic',
            'status'                    => 'draft',
        ];

        $data = $this->mutateFormDataBeforeCreate($data);

        /** @var Quiz $quiz */
        $quiz = static::getModel()::create(
            Arr::only($data, (new Quiz)->getFillable())
        );

        $this->record = $quiz;

        Notification::make()
            ->title(__('lecturer.notification_quiz_drafted'))
            ->success()
            ->send();

        $this->redirect(QuizResource::getUrl('edit', [
            'record' => $quiz,
            'step'   => 'questions',
        ]), navigate: true);
    }

    public function saveQuestion(array $data, ?string $questionId = null): array
    {
        if (! $this->record) {
            return [];
        }

        $quiz = $this->record;

        if ($questionId) {
            $q = Question::where('id', $questionId)->where('quiz_id', $quiz->id)->firstOrFail();
            $q->update([
                'type'           => $data['type'],
                'content'        => $data['content'],
                'explanation'    => $data['explanation'] ?? null,
                'marks'          => $data['marks'] ?? 1,
                'negative_marks' => $data['negative_marks'] ?? 0,
                'hint'           => $data['hint'] ?? null,
            ]);
            $q->options()->delete();
            $q->fillBlankAnswers()->delete();
        } else {
            $sortOrder = $quiz->questions()->max('sort_order') ?? 0;
            $q = Question::create([
                'quiz_id'        => $quiz->id,
                'lecturer_id'    => auth()->id(),
                'type'           => $data['type'],
                'content'        => $data['content'],
                'explanation'    => $data['explanation'] ?? null,
                'marks'          => $data['marks'] ?? 1,
                'negative_marks' => $data['negative_marks'] ?? 0,
                'hint'           => $data['hint'] ?? null,
                'sort_order'     => $sortOrder + 1,
            ]);
        }

        foreach ($data['options'] ?? [] as $i => $opt) {
            if (empty(trim($opt['content'] ?? ''))) {
                continue;
            }
            QuestionOption::create([
                'question_id' => $q->id,
                'content'     => $opt['content'],
                'is_correct'  => (bool) ($opt['is_correct'] ?? false),
                'sort_order'  => $i + 1,
            ]);
        }
        foreach ($data['blank_answers'] ?? [] as $ans) {
            if (empty(trim($ans['answer'] ?? ''))) {
                continue;
            }
            FillBlankAnswer::create(['question_id' => $q->id, 'answer' => $ans['answer']]);
        }

        $quiz->update([
            'total_questions' => $quiz->questions()->count(),
            'total_marks'     => $quiz->questions()->sum('marks'),
        ]);

        return $this->getQuestionsPayload();
    }

    public function deleteQuestion(string $questionId): array
    {
        if (! $this->record) {
            return [];
        }

        $quiz = $this->record;
        $q = Question::where('id', $questionId)->where('quiz_id', $quiz->id)->firstOrFail();
        $q->options()->delete();
        $q->fillBlankAnswers()->delete();
        $q->delete();

        $quiz->update([
            'total_questions' => $quiz->questions()->count(),
            'total_marks'     => $quiz->questions()->sum('marks'),
        ]);

        return $this->getQuestionsPayload();
    }

    public function importFromBank(array $questionIds): array
    {
        if (! $this->record) {
            return [];
        }

        $quiz = $this->record;
        $user = auth()->user();

        $service = new QuestionBankService;
        $service->importToQuiz($user->id, $questionIds, $quiz, $user);

        return $this->getQuestionsPayload();
    }

    public function reloadQuestions(): array
    {
        if (! $this->record) {
            return [];
        }

        return $this->getQuestionsPayload();
    }

    private function getQuestionsPayload(): array
    {
        return $this->record->questions()
            ->with(['options', 'fillBlankAnswers'])
            ->get()
            ->map(fn ($q) => [
                'id'                      => $q->id,
                'type'                    => $q->type,
                'content'                 => $q->content,
                'explanation'             => $q->explanation ?? '',
                'marks'                   => (float) $q->marks,
                'negative_marks'          => (float) $q->negative_marks,
                'hint'                    => $q->hint ?? '',
                'sort_order'              => $q->sort_order,
                'source_bank_question_id' => $q->source_bank_question_id ?? null,
                'options'                 => $q->options->map(fn ($o) => [
                    'content'    => $o->content,
                    'is_correct' => (bool) $o->is_correct,
                ])->values()->all(),
                'blank_answers'           => $q->fillBlankAnswers->map(fn ($a) => [
                    'answer' => $a->answer,
                ])->values()->all(),
            ])->values()->all();
    }
}
