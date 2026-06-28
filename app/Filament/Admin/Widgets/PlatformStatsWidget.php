<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalUsers    = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $creators      = User::where('role', 'creator')->count();
        $published     = Quiz::where('status', 'published')->count();
        $totalQuizzes  = Quiz::count();
        $attempts      = Attempt::count();
        $todayAttempts = Attempt::whereDate('created_at', today())->count();

        return [
            Stat::make(__('admin.widget_total_users'), number_format($totalUsers))
                ->description(__('admin.widget_new_users_today', ['count' => $newUsersToday]))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make(__('admin.widget_creators'), number_format($creators))
                ->description(__('admin.widget_quizzes_published', ['count' => $published]))
                ->descriptionIcon('heroicon-m-pencil-square')
                ->icon('heroicon-o-user-group')
                ->color('warning'),

            Stat::make(__('admin.widget_published_quizzes'), number_format($published))
                ->description(__('admin.widget_total_incl_drafts', ['count' => $totalQuizzes]))
                ->descriptionIcon('heroicon-m-document-text')
                ->icon('heroicon-o-academic-cap')
                ->color('success'),

            Stat::make(__('admin.widget_total_attempts'), number_format($attempts))
                ->description(__('admin.widget_new_users_today', ['count' => $todayAttempts]))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info'),
        ];
    }
}
