<x-filament-widgets::widget class="cc-widget">
    <div class="cc-kpi-grid">
        <a href="{{ $quizzesUrl }}" class="cc-card cc-kpi-card hover:shadow-md transition-shadow">
            <div>
                <div class="cc-kpi-value">{{ $stats['totalQuizzes'] }}</div>
                <div class="cc-kpi-label">{{ __('lecturer.stat_my_quizzes') }}</div>
                <div class="cc-kpi-desc">{{ __('lecturer.stat_published_draft', ['published' => $stats['published'], 'draft' => $stats['draft']]) }}</div>
            </div>
            <div class="cc-kpi-icon cc-kpi-icon--blue">
                <x-heroicon-o-academic-cap class="h-5 w-5" />
            </div>
        </a>

        <div class="cc-card cc-kpi-card">
            <div>
                <div class="cc-kpi-value">{{ number_format($stats['totalAttempts']) }}</div>
                <div class="cc-kpi-label">{{ __('lecturer.stat_total_attempts') }}</div>
                <div class="cc-kpi-desc">{{ number_format($stats['attemptsThisMonth']) }} {{ __('lecturer.stat_this_month') }}</div>
            </div>
            <div class="cc-kpi-icon cc-kpi-icon--green">
                <x-heroicon-o-users class="h-5 w-5" />
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
