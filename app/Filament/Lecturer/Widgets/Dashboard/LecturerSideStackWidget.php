<?php

namespace App\Filament\Lecturer\Widgets\Dashboard;

use App\Services\Lecturer\LecturerDashboardService;
use Filament\Widgets\Widget;

class LecturerSideStackWidget extends Widget
{
    protected static ?int $sort = 6;

    protected static string $view = 'filament.lecturer.widgets.dashboard.side-stack';

    protected int|string|array $columnSpan = ['default' => 'full', 'xl' => 1];

    public function getViewData(): array
    {
        $service = app(LecturerDashboardService::class)->forUser(auth()->user());

        return [
            'upcoming' => $service->getUpcomingQuizzes(5),
            'recent'   => $service->getRecentQuizzes(5),
        ];
    }
}
