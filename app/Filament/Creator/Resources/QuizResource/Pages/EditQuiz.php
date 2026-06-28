<?php

namespace App\Filament\Creator\Resources\QuizResource\Pages;

use App\Exceptions\PlanLimitExceededException;
use App\Filament\Creator\Resources\QuizResource;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\FillBlankAnswer;
use App\Services\QuestionBank\QuestionBankService;
use App\Services\Quiz\PlanLimitService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /* ── Question CRUD (called from Questions tab via $wire) ─────── */

    public function saveQuestion(array $data, ?string $questionId = null): array
    {
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
            // Enforce plan question limits on new questions only
            try {
                app(PlanLimitService::class)->assertCanAddQuestion(auth()->user(), $quiz->id);
            } catch (PlanLimitExceededException $e) {
                Notification::make()->title(__('creator.notif_plan_limit_reached'))->body($e->getMessage())->danger()->send();
                return [];
            }

            $sortOrder = $quiz->questions()->max('sort_order') ?? 0;
            $q = Question::create([
                'quiz_id'        => $quiz->id,
                'creator_id'     => auth()->id(),
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
            if (empty(trim($opt['content'] ?? ''))) continue;
            QuestionOption::create([
                'question_id' => $q->id,
                'content'     => $opt['content'],
                'is_correct'  => (bool) ($opt['is_correct'] ?? false),
                'sort_order'  => $i + 1,
            ]);
        }
        foreach ($data['blank_answers'] ?? [] as $ans) {
            if (empty(trim($ans['answer'] ?? ''))) continue;
            FillBlankAnswer::create([
                'question_id' => $q->id,
                'answer'      => $ans['answer'],
            ]);
        }

        $quiz->update([
            'total_questions' => $quiz->questions()->count(),
            'total_marks'     => $quiz->questions()->sum('marks'),
        ]);

        // Return updated question list for Alpine re-render
        return $this->getQuestionsPayload();
    }

    public function deleteQuestion(string $questionId): array
    {
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
        $quiz          = $this->record;
        $user          = auth()->user();
        $before        = $quiz->questions()->count();

        $service = new QuestionBankService();
        $service->importToQuiz($user->id, $questionIds, $quiz, $user);

        $after    = $quiz->fresh()->questions()->count();
        $imported = $after - $before;
        $skipped  = count($questionIds) - $imported;

        if ($skipped > 0 && $imported < count($questionIds)) {
            $plan = app(\App\Services\Quiz\PlanLimitService::class)->getActivePlan($user);
            Notification::make()
                ->title(__('creator.notif_plan_limit_reached'))
                ->body(__('creator.notif_import_skipped_body', ['imported' => $imported, 'skipped' => $skipped, 'plan' => $plan?->name, 'max' => $plan?->max_questions_per_quiz]))
                ->warning()
                ->send();
        }

        return $this->getQuestionsPayload();
    }

    public function reloadQuestions(): array
    {
        return $this->getQuestionsPayload();
    }

    private function getQuestionsPayload(): array
    {
        return $this->record->questions()
            ->with(['options', 'fillBlankAnswers'])
            ->get()
            ->map(fn($q) => [
                'id'                      => $q->id,
                'type'                    => $q->type,
                'content'                 => $q->content,
                'explanation'             => $q->explanation ?? '',
                'marks'                   => (float) $q->marks,
                'negative_marks'          => (float) $q->negative_marks,
                'hint'                    => $q->hint ?? '',
                'sort_order'              => $q->sort_order,
                'source_bank_question_id' => $q->source_bank_question_id ?? null,
                'options'                 => $q->options->map(fn($o) => [
                    'content'    => $o->content,
                    'is_correct' => (bool) $o->is_correct,
                ])->values()->all(),
                'blank_answers'  => $q->fillBlankAnswers->map(fn($a) => [
                    'answer' => $a->answer,
                ])->values()->all(),
            ])->values()->all();
    }
}
