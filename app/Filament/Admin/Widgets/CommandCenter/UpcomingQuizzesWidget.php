<?php

namespace App\Filament\Admin\Widgets\CommandCenter;

use App\Services\Exam\AttemptMonitoringService;
use Filament\Widgets\Widget;

class UpcomingQuizzesWidget extends Widget
{
    protected static ?int $sort = 5;

    protected static string $view = 'filament.admin.widgets.command-center.upcoming-quizzes';

    protected int|string|array $columnSpan = 1;

    public function getViewData(): array
    {
        return [
            'quizzes' => app(AttemptMonitoringService::class)->getUpcomingQuizzes(5),
        ];
    }
}
