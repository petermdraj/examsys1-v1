<?php

namespace App\Filament\Admin\Widgets\CommandCenter;

use App\Services\Exam\AttemptMonitoringService;
use Filament\Widgets\Widget;

class ExamStatusDonutWidget extends Widget
{
    protected static ?int $sort = 3;

    protected static string $view = 'filament.admin.widgets.command-center.exam-status-donut';

    protected int|string|array $columnSpan = ['default' => 'full', 'xl' => 1];

    public function getViewData(): array
    {
        return [
            'breakdown' => app(AttemptMonitoringService::class)->getExamStatusBreakdown(),
        ];
    }
}
