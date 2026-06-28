<?php

namespace App\Filament\Creator\Widgets;

use App\Settings\PlatformSettings;
use Filament\Widgets\Widget;

class AiCreditsWidget extends Widget
{
    protected static ?int $sort = 2;
    protected static string $view = 'filament.creator.widgets.ai-credits-widget';
    protected int | string | array $columnSpan = 1;

    public function getViewData(): array
    {
        $user = auth()->user();
        $sym  = app(PlatformSettings::class)->currency_symbol;

        return [
            'freeCredits'   => $user->ai_credits_free_remaining,
            'creditsUsed'   => $user->ai_credits_used,
            'walletBalance' => $sym . number_format($user->wallet_balance, 2),
            'planName'      => $user->activeSubscription?->plan?->name ?? 'No plan',
            'sym'           => $sym,
        ];
    }
}
