<?php

namespace App\Filament\Lecturer\Widgets;

use App\Models\Quiz;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LecturerStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $uid = auth()->id();

        $totalQuizzes     = Quiz::where('lecturer_id', $uid)->count();
        $publishedQuizzes = Quiz::where('lecturer_id', $uid)->where('status', 'published')->count();
        $draftQuizzes     = Quiz::where('lecturer_id', $uid)->where('status', 'draft')->count();

        $totalAttempts     = Quiz::where('lecturer_id', $uid)->sum('total_attempts');
        $attemptsThisMonth = \App\Models\Attempt::whereHas('quiz', fn ($q) => $q->where('lecturer_id', $uid))
            ->whereMonth('created_at', now()->month)
            ->count();

        return [
            Stat::make(__('lecturer.stat_my_quizzes'), $totalQuizzes)
                ->description(__('lecturer.stat_published_draft', ['published' => $publishedQuizzes, 'draft' => $draftQuizzes]))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),

            Stat::make(__('lecturer.stat_total_attempts'), number_format($totalAttempts))
                ->description(number_format($attemptsThisMonth).' '.__('lecturer.stat_this_month'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-users')
                ->color('success'),
        ];
    }
}
