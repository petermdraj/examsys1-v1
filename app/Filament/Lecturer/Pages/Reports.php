<?php

namespace App\Filament\Lecturer\Pages;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Services\Exam\ExamGradeReportService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    public static function getNavigationLabel(): string { return __('lecturer.nav_reports'); }
    public function getTitle(): string { return __('lecturer.nav_reports'); }
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.lecturer.pages.reports';

    public ?string $selectedQuizId = null;

    public string $attemptMode = 'best';

    public function mount(): void
    {
        $this->selectedQuizId = request('quiz_id') ?? Quiz::where('lecturer_id', auth()->id())
            ->where('status', 'published')
            ->value('id');
    }

    public function getViewData(): array
    {
        $quizzes = Quiz::where('lecturer_id', auth()->id())->orderBy('title')->get();
        $quiz    = null;
        $stats   = null;
        $recentAttempts = collect();
        $scoreBuckets   = [];

        if ($this->selectedQuizId) {
            $quiz = Quiz::where('id', $this->selectedQuizId)
                ->where('lecturer_id', auth()->id())
                ->first();

            if ($quiz) {
                $attempts = Attempt::with('user')
                    ->where('quiz_id', $quiz->id)
                    ->where('status', 'completed')
                    ->get();

                $total = $attempts->count();
                $passed = $attempts->where('is_passed', true)->count();

                $stats = [
                    'total_attempts' => $total,
                    'avg_score'      => round($attempts->avg('percentage') ?? 0, 1),
                    'pass_rate'      => $total ? round($passed / $total * 100, 1) : 0,
                    'avg_time'       => $total ? round($attempts->avg('time_taken_seconds') / 60, 1) : 0,
                    'passed'         => $passed,
                    'failed'         => $total - $passed,
                    'top_score'      => round($attempts->max('percentage') ?? 0, 1),
                    'lowest_score'   => round($attempts->min('percentage') ?? 0, 1),
                ];

                // Score distribution buckets: 0-20, 20-40, 40-60, 60-80, 80-100
                $bucketLabels = ['0–20%', '21–40%', '41–60%', '61–80%', '81–100%'];
                $counts = [0, 0, 0, 0, 0];
                foreach ($attempts as $a) {
                    $p = (float) $a->percentage;
                    if ($p <= 20)       $counts[0]++;
                    elseif ($p <= 40)   $counts[1]++;
                    elseif ($p <= 60)   $counts[2]++;
                    elseif ($p <= 80)   $counts[3]++;
                    else                $counts[4]++;
                }
                $maxCount = max(1, max($counts));
                foreach ($bucketLabels as $i => $label) {
                    $scoreBuckets[] = [
                        'label' => $label,
                        'count' => $counts[$i],
                        'pct'   => round($counts[$i] / $maxCount * 100),
                    ];
                }

                $recentAttempts = Attempt::with('user')
                    ->where('quiz_id', $quiz->id)
                    ->where('status', 'completed')
                    ->orderByDesc('submitted_at')
                    ->limit(8)
                    ->get();
            }
        }

        $sym = app(\App\Settings\PlatformSettings::class)->currency_symbol;
        return compact('quizzes', 'quiz', 'stats', 'recentAttempts', 'scoreBuckets', 'sym');
    }

    public function selectQuiz(string $id): void
    {
        $this->selectedQuizId = $id;
    }

    public function exportExcel()
    {
        $quiz = $this->resolveOwnedQuiz();
        if (! $quiz) {
            abort(403);
        }

        $path = app(ExamGradeReportService::class)->exportExcel($quiz, null, $this->attemptMode);

        if (! $path) {
            Notification::make()->title(__('lecturer.grade_no_data'))->warning()->send();

            return;
        }

        $filename = 'grade-report-' . Str::slug($quiz->title) . '-' . now()->format('Y-m-d') . '.xlsx';

        return response()->download(
            $path,
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        )->deleteFileAfterSend();
    }

    public function exportPdf()
    {
        $quiz = $this->resolveOwnedQuiz();
        if (! $quiz) {
            abort(403);
        }

        $rows = app(ExamGradeReportService::class)->buildRows($quiz, null, $this->attemptMode);

        if ($rows->isEmpty()) {
            Notification::make()->title(__('lecturer.grade_no_data'))->warning()->send();

            return;
        }

        return app(ExamGradeReportService::class)->exportPdf($quiz, null, $this->attemptMode);
    }

    protected function resolveOwnedQuiz(): ?Quiz
    {
        if (! $this->selectedQuizId) {
            return null;
        }

        return Quiz::query()
            ->where('id', $this->selectedQuizId)
            ->where('lecturer_id', auth()->id())
            ->first();
    }
}
