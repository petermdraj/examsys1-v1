<?php

namespace App\Filament\Admin\Widgets\CommandCenter;

use App\Services\Exam\AttemptMonitoringService;
use Filament\Widgets\Widget;

class ActiveExamHeroWidget extends Widget
{
    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '30s';

    protected static string $view = 'filament.admin.widgets.command-center.active-exam-hero';

    protected int|string|array $columnSpan = ['default' => 'full', 'xl' => 2];

    public function getViewData(): array
    {
        $stats = app(AttemptMonitoringService::class)->getCommandCenterStats();

        return [
            'activeQuiz' => $stats['activeQuiz'],
            'liveCount'  => $stats['liveCount'],
        ];
    }
}
