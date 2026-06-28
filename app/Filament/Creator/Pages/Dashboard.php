<?php

namespace App\Filament\Creator\Pages;

use App\Filament\Creator\Widgets\CreatorStatsWidget;
use App\Filament\Creator\Widgets\RecentQuizzesWidget;
use App\Filament\Creator\Widgets\RecentEarningsWidget;
use App\Filament\Creator\Widgets\AiCreditsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    public function getTitle(): string { return __('creator.nav_dashboard'); }
    public static function getNavigationLabel(): string { return __('creator.nav_dashboard'); }

    public function getWidgets(): array
    {
        return [
            CreatorStatsWidget::class,
            AiCreditsWidget::class,
            RecentQuizzesWidget::class,
            RecentEarningsWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
