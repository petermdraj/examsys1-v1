<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\AIUsageWidget;
use App\Filament\Admin\Widgets\PlatformStatsWidget;
use App\Filament\Admin\Widgets\RecentOrdersWidget;
use App\Filament\Admin\Widgets\RevenueWidget;
use App\Filament\Admin\Widgets\TopCreatorsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $routePath = '/';

    public function getWidgets(): array
    {
        return [
            PlatformStatsWidget::class,
            RevenueWidget::class,
            AIUsageWidget::class,
            TopCreatorsWidget::class,
            RecentOrdersWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
