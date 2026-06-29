<?php

namespace App\Filament\Lecturer\Widgets\Dashboard;

use App\Services\Lecturer\LecturerDashboardService;
use Filament\Widgets\Widget;

class AttemptsPerformanceChartWidget extends Widget
{
    protected static ?int $sort = 5;

    protected static string $view = 'filament.lecturer.widgets.dashboard.attempts-performance-chart';

    protected int|string|array $columnSpan = ['default' => 'full', 'xl' => 2];

    public function getViewData(): array
    {
        return [
            'chartData' => app(LecturerDashboardService::class)
                ->forUser(auth()->user())
                ->getMonthlyAttemptsChart(),
        ];
    }
}
