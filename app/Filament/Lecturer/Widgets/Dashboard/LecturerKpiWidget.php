<?php

namespace App\Filament\Lecturer\Widgets\Dashboard;

use App\Filament\Lecturer\Resources\QuizResource;
use App\Services\Lecturer\LecturerDashboardService;
use Filament\Widgets\Widget;

class LecturerKpiWidget extends Widget
{
    protected static ?int $sort = 1;

    protected static string $view = 'filament.lecturer.widgets.dashboard.kpi-cards';

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $stats = app(LecturerDashboardService::class)
            ->forUser(auth()->user())
            ->getKpiStats();

        return [
            'stats'    => $stats,
            'quizzesUrl' => QuizResource::getUrl('index'),
        ];
    }
}
