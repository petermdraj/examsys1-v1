<?php

namespace App\Filament\Admin\Widgets\CommandCenter;

use App\Services\Exam\AttemptMonitoringService;
use Filament\Widgets\Widget;

class PerformanceChartWidget extends Widget
{
    protected static ?int $sort = 4;

    protected static string $view = 'filament.admin.widgets.command-center.performance-chart';

    protected int|string|array $columnSpan = ['default' => 'full', 'xl' => 2];

    public function getViewData(): array
    {
        return [
            'chartData' => app(AttemptMonitoringService::class)->getMonthlyChartData(),
        ];
    }
}
