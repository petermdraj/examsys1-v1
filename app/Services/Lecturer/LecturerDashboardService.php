<?php

namespace App\Services\Lecturer;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LecturerDashboardService
{
    public function forUser(User $user): self
    {
        $this->userId = $user->id;

        return $this;
    }

    protected string $userId;

    public function getKpiStats(): array
    {
        $totalQuizzes = Quiz::where('lecturer_id', $this->userId)->count();
        $published = Quiz::where('lecturer_id', $this->userId)->where('status', 'published')->count();
        $draft = Quiz::where('lecturer_id', $this->userId)->where('status', 'draft')->count();

        $totalAttempts = (int) Quiz::where('lecturer_id', $this->userId)->sum('total_attempts');
        $attemptsThisMonth = Attempt::whereHas('quiz', fn ($q) => $q->where('lecturer_id', $this->userId))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return compact('totalQuizzes', 'published', 'draft', 'totalAttempts', 'attemptsThisMonth');
    }

    public function getFeaturedQuiz(): ?Quiz
    {
        $quiz = Quiz::where('lecturer_id', $this->userId)
            ->with('category')
            ->withCount([
                'attempts as active_count' => fn ($q) => $q->whereIn('status', ['in_progress', 'paused']),
            ])
            ->orderByDesc('total_attempts')
            ->orderByDesc('updated_at')
            ->first();

        if ($quiz) {
            $stats = $this->getQuizAttemptStats($quiz->id);
            $quiz->setAttribute('pass_rate', $stats['pass_rate']);
            $quiz->setAttribute('avg_duration_min', $stats['avg_duration_min']);
        }

        return $quiz;
    }

    public function getExamPerformance(int $limit = 10): Collection
    {
        $quizzes = Quiz::where('lecturer_id', $this->userId)
            ->orderByDesc('total_attempts')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();

        if ($quizzes->isEmpty()) {
            return collect();
        }

        $statsByQuiz = Attempt::query()
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->where('status', 'completed')
            ->selectRaw('quiz_id, COUNT(*) as total, SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END) as passed, AVG(time_taken_seconds) as avg_seconds')
            ->groupBy('quiz_id')
            ->get()
            ->keyBy('quiz_id');

        return $quizzes->map(function (Quiz $quiz) use ($statsByQuiz) {
            $row = $statsByQuiz->get($quiz->id);
            $total = (int) ($row?->total ?? 0);

            return [
                'quiz'             => $quiz,
                'attempts'         => $total,
                'pass_rate'        => $total > 0 ? round(((int) $row->passed / $total) * 100, 1) : null,
                'avg_duration_min' => $total > 0 && $row->avg_seconds
                    ? round($row->avg_seconds / 60, 1)
                    : null,
            ];
        });
    }

    protected function getQuizAttemptStats(string $quizId): array
    {
        $row = Attempt::query()
            ->where('quiz_id', $quizId)
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END) as passed, AVG(time_taken_seconds) as avg_seconds')
            ->first();

        $total = (int) ($row?->total ?? 0);

        return [
            'pass_rate'        => $total > 0 ? round(((int) $row->passed / $total) * 100, 1) : null,
            'avg_duration_min' => $total > 0 && $row->avg_seconds
                ? round($row->avg_seconds / 60, 1)
                : null,
        ];
    }

    public function getQuizStatusBreakdown(): array
    {
        $published = Quiz::where('lecturer_id', $this->userId)->where('status', 'published')->count();
        $scheduled = Quiz::where('lecturer_id', $this->userId)->where('status', 'scheduled')->count();
        $draft = Quiz::where('lecturer_id', $this->userId)->where('status', 'draft')->count();
        $archived = Quiz::where('lecturer_id', $this->userId)->where('status', 'archived')->count();
        $total = $published + $scheduled + $draft + $archived;

        $publishedPct = $total > 0 ? round(($published / $total) * 100) : 0;

        $publishedDeg = $total > 0 ? ($published / $total) * 360 : 0;
        $scheduledDeg = $total > 0 ? ($scheduled / $total) * 360 : 0;
        $draftDeg = $total > 0 ? ($draft / $total) * 360 : 0;

        $gradient = $total > 0
            ? sprintf(
                'conic-gradient(#2E9E68 0deg %.1fdeg, #6366f1 %.1fdeg %.1fdeg, #9ca3af %.1fdeg %.1fdeg, #d1d5db %.1fdeg 360deg)',
                $publishedDeg,
                $publishedDeg,
                $publishedDeg + $scheduledDeg,
                $publishedDeg + $scheduledDeg,
                $publishedDeg + $scheduledDeg + $draftDeg,
                $publishedDeg + $scheduledDeg + $draftDeg
            )
            : 'conic-gradient(#e5e7eb 0deg 360deg)';

        return [
            'published'     => $published,
            'scheduled'     => $scheduled,
            'draft'         => $draft,
            'archived'      => $archived,
            'total'         => $total,
            'published_pct' => $publishedPct,
            'gradient'      => $gradient,
        ];
    }

    public function getMonthlyAttemptsChart(): array
    {
        $data = [];
        $months = collect(range(11, 0))->map(fn ($i) => Carbon::today()->startOfMonth()->subMonths($i));

        foreach ($months as $month) {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $attempts = Attempt::whereHas('quiz', fn ($q) => $q->where('lecturer_id', $this->userId))
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $data[] = [
                'label'          => $month->format('M'),
                'full_date'      => $month->format('F Y'),
                'attempts_val'   => $attempts,
                'is_current'     => $month->isCurrentMonth(),
            ];
        }

        $maxAttempts = max(1, collect($data)->max('attempts_val'));

        return array_map(function (array $row) use ($maxAttempts) {
            $row['attempts_height'] = $row['attempts_val'] > 0
                ? max(4, round(($row['attempts_val'] / $maxAttempts) * 100))
                : 4;

            return $row;
        }, $data);
    }

    public function getUpcomingQuizzes(int $limit = 5): Collection
    {
        return Quiz::where('lecturer_id', $this->userId)
            ->whereIn('status', ['published', 'scheduled'])
            ->whereNotNull('start_at')
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->limit($limit)
            ->with('category')
            ->get();
    }

    public function getRecentQuizzes(int $limit = 5): Collection
    {
        return Quiz::where('lecturer_id', $this->userId)
            ->with('category')
            ->latest()
            ->limit($limit)
            ->get();
    }

}
