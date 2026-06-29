<?php

namespace App\Filament\Admin\Widgets\CommandCenter;

use App\Services\Exam\AttemptMonitoringService;
use Filament\Widgets\Widget;

class CommandCenterSideStackWidget extends Widget
{
    protected static ?int $sort = 5;

    protected static ?string $pollingInterval = '30s';

    protected static string $view = 'filament.admin.widgets.command-center.side-stack';

    protected int|string|array $columnSpan = ['default' => 'full', 'xl' => 1];

    public function getViewData(): array
    {
        $monitoring = app(AttemptMonitoringService::class);
        $alerts = $monitoring->getRiskAlerts(5);

        return [
            'alerts'  => $alerts,
            'count'   => $alerts->count(),
            'quizzes' => $monitoring->getUpcomingQuizzes(5),
        ];
    }
}
