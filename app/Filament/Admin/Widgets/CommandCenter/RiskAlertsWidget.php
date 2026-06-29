<?php

namespace App\Filament\Admin\Widgets\CommandCenter;

use App\Services\Exam\AttemptMonitoringService;
use Filament\Widgets\Widget;

class RiskAlertsWidget extends Widget
{
    protected static ?int $sort = 4;

    protected static string $view = 'filament.admin.widgets.command-center.risk-alerts';

    protected int|string|array $columnSpan = 1;

    public function getViewData(): array
    {
        $alerts = app(AttemptMonitoringService::class)->getRiskAlerts(5);

        return [
            'alerts' => $alerts,
            'count'  => $alerts->count(),
        ];
    }
}
