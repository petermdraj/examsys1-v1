<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\AIUsageWidget;
use App\Filament\Admin\Widgets\PlatformStatsWidget;
use App\Filament\Admin\Widgets\TopLecturersWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $routePath = '/';

    public function getWidgets(): array
    {
        return [
            PlatformStatsWidget::class,
            AIUsageWidget::class,
            TopLecturersWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}
