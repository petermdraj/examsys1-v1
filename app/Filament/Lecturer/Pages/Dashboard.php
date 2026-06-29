<?php

namespace App\Filament\Lecturer\Pages;

use App\Filament\Lecturer\Widgets\Dashboard\AttemptsPerformanceChartWidget;
use App\Filament\Lecturer\Widgets\Dashboard\ExamPerformanceWidget;
use App\Filament\Lecturer\Widgets\Dashboard\FeaturedQuizHeroWidget;
use App\Filament\Lecturer\Widgets\Dashboard\LecturerKpiWidget;
use App\Filament\Lecturer\Widgets\Dashboard\LecturerSideStackWidget;
use App\Filament\Lecturer\Widgets\Dashboard\QuizStatusDonutWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getTitle(): string
    {
        return __('lecturer.nav_dashboard');
    }

    public static function getNavigationLabel(): string
    {
        return __('lecturer.nav_dashboard');
    }

    public function getWidgets(): array
    {
        return [
            LecturerKpiWidget::class,
            FeaturedQuizHeroWidget::class,
            ExamPerformanceWidget::class,
            QuizStatusDonutWidget::class,
            AttemptsPerformanceChartWidget::class,
            LecturerSideStackWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'md' => 2, 'xl' => 3];
    }
}
