<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\CommandCenter\ActiveExamHeroWidget;
use App\Filament\Admin\Widgets\CommandCenter\CommandCenterKpiWidget;
use App\Filament\Admin\Widgets\CommandCenter\CommandCenterSideStackWidget;
use App\Filament\Admin\Widgets\CommandCenter\ExamStatusDonutWidget;
use App\Filament\Admin\Widgets\CommandCenter\PerformanceChartWidget;
use App\Filament\Admin\Widgets\TopLecturersWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 0;

    protected static string $routePath = '/';

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_command_center');
    }

    public function getTitle(): string
    {
        return __('admin.nav_command_center');
    }

    public function getWidgets(): array
    {
        return [
            CommandCenterKpiWidget::class,
            ActiveExamHeroWidget::class,
            ExamStatusDonutWidget::class,
            PerformanceChartWidget::class,
            CommandCenterSideStackWidget::class,
            TopLecturersWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return ['default' => 1, 'md' => 2, 'xl' => 3];
    }
}
