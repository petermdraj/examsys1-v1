<?php

namespace App\Services\Exam;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttemptMonitoringService
{
    public function getCommandCenterStats(): array
    {
        $liveCount = Attempt::whereIn('status', ['in_progress', 'paused'])->count();

        $totalStudents = User::where('role', 'student')->count();
        $newStudents = User::where('role', 'student')
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->count();
        $growthPercentage = $totalStudents > 0
            ? round(($newStudents / $totalStudents) * 100, 1)
            : 0;

        $completed = Attempt::where('status', 'completed')->get();
        $passed = $completed->where('is_passed', true)->count();
        $passRate = $completed->count() > 0
            ? round(($passed / $completed->count()) * 100, 1)
            : 0;

        $topQuizId = Attempt::whereIn('status', ['in_progress', 'paused'])
            ->select('quiz_id', DB::raw('count(*) as total'))
            ->groupBy('quiz_id')
            ->orderByDesc('total')
            ->first();

        $activeQuiz = null;
        if ($topQuizId) {
            $activeQuiz = Quiz::with('category')
                ->withCount(['attempts as active_count' => fn ($q) => $q->whereIn('status', ['in_progress', 'paused'])])
                ->find($topQuizId->quiz_id);
        }

        return compact('liveCount', 'totalStudents', 'growthPercentage', 'passRate', 'activeQuiz');
    }

    public function getMonthlyChartData(): array
    {
        $data = [];
        $months = collect(range(11, 0))->map(fn ($i) => Carbon::today()->startOfMonth()->subMonths($i));

        foreach ($months as $month) {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $attendance = Attempt::whereBetween('created_at', [$start, $end])->count();

            $monthAttempts = Attempt::whereBetween('updated_at', [$start, $end])
                ->where('status', 'completed')
                ->get();

            $passRate = $monthAttempts->count() > 0
                ? round(($monthAttempts->where('is_passed', true)->count() / $monthAttempts->count()) * 100)
                : 0;

            $data[] = [
                'label'          => $month->format('M'),
                'full_date'      => $month->format('F Y'),
                'attendance_val' => $attendance,
                'pass_val'       => $passRate,
                'is_current'     => $month->isCurrentMonth(),
            ];
        }

        $maxAttendance = max(1, collect($data)->max('attendance_val'));

        return array_map(function (array $row) use ($maxAttendance) {
            $row['attendance_height'] = $row['attendance_val'] > 0
                ? max(4, round(($row['attendance_val'] / $maxAttendance) * 100))
                : 4;
            $row['pass_height'] = $row['pass_val'] > 0
                ? max(4, $row['pass_val'])
                : 4;

            return $row;
        }, $data);
    }

    public function getExamStatusBreakdown(): array
    {
        $published = Quiz::where('status', 'published')->count();
        $scheduled = Quiz::where('status', 'scheduled')->count();
        $draft = Quiz::where('status', 'draft')->count();
        $archived = Quiz::where('status', 'archived')->count();
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

    public function getRiskAlerts(int $limit = 5): Collection
    {
        return Attempt::whereIn('status', ['in_progress', 'paused'])
            ->whereIn('risk_level', ['critical', 'warning'])
            ->with(['user', 'quiz'])
            ->orderByDesc('risk_score')
            ->limit($limit)
            ->get()
            ->map(function (Attempt $attempt) {
                $events = $attempt->flagged_events ?? [];
                $latest = ! empty($events) ? end($events) : __('admin.risk_general');

                return (object) [
                    'id'         => $attempt->id,
                    'level'      => $attempt->risk_level,
                    'title'      => $this->riskTitle($latest),
                    'message'    => $latest . ' (' . $attempt->user->name . ')',
                    'user_name'  => $attempt->user->name,
                    'quiz_title' => $attempt->quiz->title,
                ];
            });
    }

    public function getUpcomingQuizzes(int $limit = 5): Collection
    {
        return Quiz::whereIn('status', ['published', 'scheduled'])
            ->whereNotNull('start_at')
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->limit($limit)
            ->with('category')
            ->get();
    }

    public function getLiveKpis(): array
    {
        return [
            'active_users'    => Attempt::where('status', 'in_progress')->count(),
            'critical_risk'   => Attempt::whereIn('status', ['in_progress', 'paused'])->where('risk_level', 'critical')->count(),
            'paused'          => Attempt::where('status', 'paused')->count(),
            'completed_today' => Attempt::whereIn('status', ['completed', 'terminated'])
                ->whereDate('updated_at', Carbon::today())
                ->count(),
        ];
    }

    public function getLiveSessions(array $filters = []): Collection
    {
        $query = Attempt::with(['user', 'quiz', 'answers'])
            ->whereIn('status', ['in_progress', 'paused']);

        if (! empty($filters['quiz_id']) && $filters['quiz_id'] !== 'all') {
            $query->where('quiz_id', $filters['quiz_id']);
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['risk_level']) && $filters['risk_level'] !== 'all') {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderByDesc('risk_score')
            ->orderByRaw('COALESCE(last_activity_at, started_at) DESC')
            ->get();
    }

    protected function riskTitle(string $message): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'tab')) {
            return __('admin.risk_tab_switch');
        }

        if (str_contains($lower, 'ip') || str_contains($lower, 'location')) {
            return __('admin.risk_ip_mismatch');
        }

        if (str_contains($lower, 'blur') || str_contains($lower, 'focus') || str_contains($lower, 'window')) {
            return __('admin.risk_window_blur');
        }

        return __('admin.risk_general');
    }
}
