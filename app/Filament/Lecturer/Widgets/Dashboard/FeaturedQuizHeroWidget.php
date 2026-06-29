<?php

namespace App\Filament\Lecturer\Widgets\Dashboard;

use App\Services\Lecturer\LecturerDashboardService;
use Filament\Widgets\Widget;

class FeaturedQuizHeroWidget extends Widget
{
    protected static ?int $sort = 2;

    protected static string $view = 'filament.lecturer.widgets.dashboard.featured-quiz-hero';

    protected int|string|array $columnSpan = ['default' => 'full', 'xl' => 2];

    public function getViewData(): array
    {
        return [
            'quiz' => app(LecturerDashboardService::class)
                ->forUser(auth()->user())
                ->getFeaturedQuiz(),
        ];
    }
}
