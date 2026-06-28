<?php

namespace App\Filament\Creator\Widgets;

use App\Models\Order;
use App\Models\Quiz;
use App\Settings\PlatformSettings;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CreatorStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $uid = auth()->id();
        $sym = app(PlatformSettings::class)->currency_symbol;

        $totalQuizzes     = Quiz::where('creator_id', $uid)->count();
        $publishedQuizzes = Quiz::where('creator_id', $uid)->where('status', 'published')->count();
        $draftQuizzes     = Quiz::where('creator_id', $uid)->where('status', 'draft')->count();

        $totalAttempts    = Quiz::where('creator_id', $uid)->sum('total_attempts');
        $attemptsThisMonth = \App\Models\Attempt::whereHas('quiz', fn ($q) => $q->where('creator_id', $uid))
            ->whereMonth('created_at', now()->month)
            ->count();

        $totalEarnings    = Order::whereHas('quiz', fn ($q) => $q->where('creator_id', $uid))
            ->where('status', 'paid')->sum('creator_earning');
        $earningsThisMonth = Order::whereHas('quiz', fn ($q) => $q->where('creator_id', $uid))
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->sum('creator_earning');

        $totalSales       = Order::whereHas('quiz', fn ($q) => $q->where('creator_id', $uid))
            ->where('status', 'paid')->count();

        return [
            Stat::make(__('creator.stat_my_quizzes'), $totalQuizzes)
                ->description(__('creator.stat_published_draft', ['published' => $publishedQuizzes, 'draft' => $draftQuizzes]))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),

            Stat::make(__('creator.stat_total_attempts'), number_format($totalAttempts))
                ->description(number_format($attemptsThisMonth) . ' ' . __('creator.stat_this_month'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-users')
                ->color('success'),

            Stat::make(__('creator.stat_total_earnings'), $sym . number_format($totalEarnings, 2))
                ->description($sym . number_format($earningsThisMonth, 2) . ' ' . __('creator.stat_this_month'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->icon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make(__('creator.stat_total_sales'), number_format($totalSales))
                ->description(__('creator.stat_paid_quiz_purchases'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->icon('heroicon-o-shopping-bag')
                ->color('info'),
        ];
    }
}
