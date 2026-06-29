<?php

namespace App\Filament\Lecturer\Widgets\Dashboard;

use App\Services\Lecturer\LecturerDashboardService;
use Filament\Widgets\Widget;

class QuizStatusDonutWidget extends Widget
{
    protected static ?int $sort = 4;

    protected static string $view = 'filament.lecturer.widgets.dashboard.quiz-status-donut';

    protected int|string|array $columnSpan = ['default' => 'full', 'xl' => 1];

    public function getViewData(): array
    {
        return [
            'breakdown' => app(LecturerDashboardService::class)
                ->forUser(auth()->user())
                ->getQuizStatusBreakdown(),
        ];
    }
}
