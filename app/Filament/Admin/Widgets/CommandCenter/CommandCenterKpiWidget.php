<?php

namespace App\Filament\Admin\Widgets\CommandCenter;

use App\Filament\Admin\Pages\LiveMonitoring;
use App\Services\Exam\AttemptMonitoringService;
use Filament\Widgets\Widget;

class CommandCenterKpiWidget extends Widget
{
    protected static ?int $sort = 1;

    protected static string $view = 'filament.admin.widgets.command-center.kpi-cards';

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $monitoring = app(AttemptMonitoringService::class);
        $stats = $monitoring->getCommandCenterStats();

        $liveLabel = $stats['liveCount'] > 0
            ? __('admin.cc_live_now', ['count' => $stats['liveCount']])
            : __('admin.cc_no_live');

        return [
            'liveCount'          => $stats['liveCount'],
            'liveLabel'          => $liveLabel,
            'liveUrl'            => LiveMonitoring::getUrl(),
            'totalStudents'      => number_format($stats['totalStudents']),
            'growthPercentage'   => $stats['growthPercentage'],
            'passRate'           => $stats['passRate'],
        ];
    }
}
