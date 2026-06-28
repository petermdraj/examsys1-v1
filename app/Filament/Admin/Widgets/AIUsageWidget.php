<?php

namespace App\Filament\Admin\Widgets;

use App\Models\AiGenerationLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AIUsageWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $today      = AiGenerationLog::whereDate('created_at', today())->count();
        $successful = AiGenerationLog::whereDate('created_at', today())->where('status', 'success')->count();
        $failed     = AiGenerationLog::whereDate('created_at', today())->where('status', 'failed')->count();
        $total      = AiGenerationLog::count();
        $totalTokens = AiGenerationLog::sum('tokens_used');

        $topUser = AiGenerationLog::selectRaw('user_id, count(*) as cnt')
            ->groupBy('user_id')
            ->orderByDesc('cnt')
            ->with('user:id,name')
            ->first();

        return [
            Stat::make(__('admin.widget_ai_today'), $today)
                ->description(__('admin.widget_ai_succeeded_failed', ['ok' => $successful, 'fail' => $failed]))
                ->descriptionIcon('heroicon-m-check-circle')
                ->icon('heroicon-o-sparkles')
                ->color($failed > 0 ? 'warning' : 'success'),

            Stat::make(__('admin.widget_ai_total'), number_format($total))
                ->description(__('admin.widget_ai_tokens_used', ['count' => number_format($totalTokens)]))
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary'),

            Stat::make(__('admin.widget_ai_top_generator'), $topUser?->user?->name ?? '—')
                ->description($topUser ? __('admin.widget_ai_total_gens', ['count' => $topUser->cnt]) : __('admin.widget_ai_no_usage'))
                ->descriptionIcon('heroicon-m-star')
                ->icon('heroicon-o-user')
                ->color('info'),
        ];
    }
}
