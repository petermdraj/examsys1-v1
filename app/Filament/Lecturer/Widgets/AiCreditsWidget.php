<?php

namespace App\Filament\Lecturer\Widgets;

use Filament\Widgets\Widget;

class AiCreditsWidget extends Widget
{
    protected static ?int $sort = 2;

    protected static string $view = 'filament.lecturer.widgets.ai-credits-widget';

    protected int|string|array $columnSpan = 1;

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'freeCredits' => $user->ai_credits_free_remaining,
            'creditsUsed' => $user->ai_credits_used,
        ];
    }
}
