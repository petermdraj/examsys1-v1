<?php

namespace App\Filament\Lecturer\Widgets\Dashboard;

use App\Services\Lecturer\LecturerDashboardService;
use Filament\Widgets\Widget;

class ExamPerformanceWidget extends Widget
{
    protected static ?int $sort = 3;

    protected static string $view = 'filament.lecturer.widgets.dashboard.exam-performance';

    protected int|string|array $columnSpan = ['default' => 'full', 'xl' => 2];

    public function getViewData(): array
    {
        return [
            'rows'       => app(LecturerDashboardService::class)
                ->forUser(auth()->user())
                ->getExamPerformance(10),
            'reportsUrl' => \App\Filament\Lecturer\Pages\Reports::getUrl(),
        ];
    }
}
