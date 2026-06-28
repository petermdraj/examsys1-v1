<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Settings\PlatformSettings;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $sym = app(PlatformSettings::class)->currency_symbol;

        $today      = Order::where('status', 'paid')->whereDate('paid_at', today())->sum('platform_commission');
        $yesterday  = Order::where('status', 'paid')->whereDate('paid_at', today()->subDay())->sum('platform_commission');
        $week       = Order::where('status', 'paid')->where('paid_at', '>=', now()->subDays(7))->sum('platform_commission');
        $prevWeek   = Order::where('status', 'paid')->whereBetween('paid_at', [now()->subDays(14), now()->subDays(7)])->sum('platform_commission');
        $month      = Order::where('status', 'paid')->where('paid_at', '>=', now()->subDays(30))->sum('platform_commission');
        $total      = Order::where('status', 'paid')->sum('platform_commission');
        $totalOrders = Order::where('status', 'paid')->count();

        $dayTrend  = $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100) : 0;
        $weekTrend = $prevWeek  > 0 ? round((($week  - $prevWeek)  / $prevWeek)  * 100) : 0;

        return [
            Stat::make(__('admin.widget_revenue_today'), $sym . number_format($today, 2))
                ->description($dayTrend >= 0 ? __('admin.widget_trend_up', ['pct' => $dayTrend]) : __('admin.widget_trend_down', ['pct' => abs($dayTrend)]))
                ->descriptionIcon($dayTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->icon('heroicon-o-arrow-trending-up')
                ->color($dayTrend >= 0 ? 'success' : 'danger'),

            Stat::make(__('admin.widget_revenue_7days'), $sym . number_format($week, 2))
                ->description($weekTrend >= 0 ? __('admin.widget_trend_up_week', ['pct' => $weekTrend]) : __('admin.widget_trend_down_week', ['pct' => abs($weekTrend)]))
                ->descriptionIcon($weekTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->icon('heroicon-o-calendar')
                ->color('info'),

            Stat::make(__('admin.widget_revenue_30days'), $sym . number_format($month, 2))
                ->description(__('admin.widget_commission_earned'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->icon('heroicon-o-chart-bar')
                ->color('primary'),

            Stat::make(__('admin.widget_revenue_alltime'), $sym . number_format($total, 2))
                ->description(__('admin.widget_paid_orders_total', ['count' => number_format($totalOrders)]))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->icon('heroicon-o-banknotes')
                ->color('warning'),
        ];
    }
}
