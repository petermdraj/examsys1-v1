<?php

namespace App\Livewire;

use App\Models\Attempt;
use App\Services\Exam\ScoringService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ExamPanel extends Component
{
    public Attempt $attempt;
    public array $questions = [];
    public array $answers = [];
    public array $markedForReview = [];
    public int $durationSeconds = 0;

    public function mount(Attempt $attempt): void
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_unless($attempt->status === 'in_progress', 404);

        $this->attempt = $attempt;
        $quiz = $attempt->quiz()->with(
            'questions.options', 'questions.fillBlankAnswers',
            'bankQuestions.options', 'bankQuestions.fillBlankAnswers'
        )->first();

        $qs = $quiz->allQuestions();
        if ($quiz->shuffle_questions) {
            $qs = $qs->shuffle()->values();
        }

        $this->questions = $qs->map(fn($q) => [
            'id'             => $q->id,
            'type'           => $q->type,
            'content'        => $q->content,
            'marks'          => (float) $q->marks,
            'negative_marks' => (float) $q->negative_marks,
            'hint'           => $q->hint,
            'options'        => $q->options->map(fn($o) => [
                'id'      => $o->id,
                'content' => $o->content,
            ])->toArray(),
        ])->toArray();

        $existingAnswers = $attempt->answers()->get()->keyBy('question_id');

        foreach ($this->questions as $q) {
            $saved = $existingAnswers->get($q['id']);
            $this->answers[$q['id']] = $saved ? ($saved->selected_options ?? []) : [];
            if ($saved && $saved->is_marked_for_review) {
                $this->markedForReview[] = $q['id'];
            }
        }

        $this->durationSeconds = $quiz->duration_minutes ? $quiz->duration_minutes * 60 : 0;
    }

    public function submit(): mixed
    {
        abort_unless($this->attempt->user_id === auth()->id(), 403);

        if ($this->attempt->status !== 'in_progress') {
            return redirect()->route('attempt.result', $this->attempt);
        }

        try {
            app(ScoringService::class)->evaluate($this->attempt);
        } catch (\Throwable $e) {
            Log::error('ExamPanel submit error', ['attempt' => $this->attempt->id, 'error' => $e->getMessage()]);
            $this->dispatch('submit-error', message: 'Submission failed. Please try again.');
            return null;
        }

        return redirect()->route('attempt.result', $this->attempt);
    }

    public function render()
    {
        return view('livewire.exam-panel');
    }
}
