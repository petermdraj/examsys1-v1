<?php

namespace App\Filament\Lecturer\Pages;

use App\Filament\Lecturer\Widgets\LecturerStatsWidget;
use App\Filament\Lecturer\Widgets\RecentQuizzesWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    public function getTitle(): string { return __('lecturer.nav_dashboard'); }
    public static function getNavigationLabel(): string { return __('lecturer.nav_dashboard'); }

    public function getWidgets(): array
    {
        return [
            LecturerStatsWidget::class,
            RecentQuizzesWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
