<?php
namespace App\Services\Exam;

use App\Jobs\GenerateCertificateJob;
use App\Jobs\RecalculateQuizStatsJob;
use App\Jobs\SendAttemptResultMailJob;
use App\Models\Attempt;

class ScoringService
{
    public function evaluate(Attempt $attempt, ?int $elapsedSeconds = null): Attempt
    {
        $attempt->load(['quiz', 'answers.question.options', 'answers.question.fillBlankAnswers']);
        // Use the quiz's total_marks so unvisited questions are counted in the denominator.
        // Accumulating only from $attempt->answers inflates percentages when users skip questions.
        $totalMarks = (float) $attempt->quiz->total_marks;
        $score = 0;

        foreach ($attempt->answers as $answer) {
            $question = $answer->question;

            // Question may have been deleted after attempt started
            if (! $question) {
                continue;
            }
            $correct = false;

            if (in_array($question->type, ['mcq_single', 'true_false'])) {
                $correctOptionId = $question->options->where('is_correct', true)->first()?->id;
                $selected = is_array($answer->selected_options) ? ($answer->selected_options[0] ?? null) : null;
                $correct = $selected && $selected === $correctOptionId;
            } elseif ($question->type === 'mcq_multiple') {
                $correctIds = $question->options->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
                $selected = collect($answer->selected_options ?? [])->sort()->values()->toArray();
                $correct = $selected === $correctIds;
            } elseif (in_array($question->type, ['fill_blank', 'short_answer'])) {
                $textAnswer = strtolower(trim($answer->text_answer ?? ''));
                $correct = $question->fillBlankAnswers->contains(function ($fb) use ($textAnswer) {
                    if ($fb->is_regex) {
                        $result = @preg_match('/' . $fb->answer . '/i', $textAnswer);

                        return $result === 1;
                    }

                    return strtolower(trim($fb->answer)) === $textAnswer;
                });
            }

            if ($correct) {
                $marksEarned = $question->marks;
            } elseif ($attempt->quiz->negative_marking_enabled && $answer->selected_options) {
                $marksEarned = -$question->negative_marks;
            } else {
                $marksEarned = 0;
            }

            // Store actual earned value (can be negative) for accurate reporting
            $answer->update(['is_correct' => $correct, 'marks_earned' => $marksEarned]);
            $score += $marksEarned;
        }

        $score = max(0, $score);
        $percentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100, 2) : 0;
        $serverDiff = now()->diffInSeconds($attempt->started_at);
        $timeTaken = ($elapsedSeconds !== null && $elapsedSeconds > 0 && $elapsedSeconds <= $serverDiff + 10)
            ? $elapsedSeconds
            : $serverDiff;

        $attempt->update([
            'status'             => 'completed',
            'submitted_at'       => now(),
            'score'              => $score,
            'total_marks'        => $totalMarks,
            'percentage'         => $percentage,
            'is_passed'          => $percentage >= $attempt->quiz->pass_percentage,
            'time_taken_seconds' => $timeTaken,
        ]);

        $attempt->quiz->increment('total_attempts');

        $fresh = $attempt->fresh(['quiz', 'user']);

        SendAttemptResultMailJob::dispatch($fresh->user, $fresh);
        RecalculateQuizStatsJob::dispatch($fresh->quiz)->delay(now()->addMinutes(5));

        if ($fresh->is_passed && $fresh->quiz->certificate_enabled) {
            GenerateCertificateJob::dispatch($fresh);
        }

        return $fresh;
    }
}
