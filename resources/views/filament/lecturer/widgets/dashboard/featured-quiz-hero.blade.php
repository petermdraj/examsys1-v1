<x-filament-widgets::widget class="cc-widget">
    @if($quiz)
        <div class="cc-card overflow-hidden">
            <div class="rounded-xl bg-gradient-to-br from-primary-700 to-primary-950 p-6 text-white">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                        <x-heroicon-o-star class="h-3.5 w-3.5" />
                        {{ __('lecturer.dash_top_quiz') }}
                    </span>
                    <span class="text-xs font-mono text-white/60 bg-white/10 px-2 py-0.5 rounded">#{{ $quiz->id }}</span>
                </div>
                <h3 class="text-xl font-bold truncate">{{ $quiz->title }}</h3>
                <div class="mt-2">
                    @if($quiz->category?->name)
                        <span class="inline-flex text-xs font-medium px-2 py-0.5 rounded-full bg-white/15 text-white/90">
                            {{ $quiz->category->name }}
                        </span>
                    @endif
                    <span class="inline-flex text-xs font-medium px-2 py-0.5 rounded-full bg-white/10 text-white/80 ml-1">
                        {{ __('lecturer.status_' . $quiz->status) }}
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($quiz->total_attempts) }}</div>
                        <div class="text-xs uppercase tracking-wide text-white/70">{{ __('lecturer.col_attempts') }}</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $quiz->active_count }}</div>
                        <div class="text-xs uppercase tracking-wide text-white/70">{{ __('lecturer.dash_active_now') }}</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-amber-300">
                            {{ $quiz->pass_rate !== null ? $quiz->pass_rate . '%' : '—' }}
                        </div>
                        <div class="text-xs uppercase tracking-wide text-white/70">{{ __('lecturer.dash_pass_rate') }}</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-sky-200">
                            {{ $quiz->avg_duration_min !== null ? $quiz->avg_duration_min . ' ' . __('lecturer.dash_min_abbr') : '—' }}
                        </div>
                        <div class="text-xs uppercase tracking-wide text-white/70">{{ __('lecturer.dash_avg_duration') }}</div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-white/10 flex gap-4">
                    <a href="{{ \App\Filament\Lecturer\Resources\QuizResource::getUrl('edit', ['record' => $quiz]) }}" class="text-xs text-white/80 hover:text-white underline">
                        {{ __('lecturer.dash_manage_quiz') }} →
                    </a>
                    <a href="{{ \App\Filament\Lecturer\Pages\Reports::getUrl(['quiz_id' => $quiz->id]) }}" class="text-xs text-white/80 hover:text-white underline">
                        {{ __('lecturer.nav_reports') }} →
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="cc-card p-8 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-50 dark:bg-primary-900/30 mb-3">
                <x-heroicon-o-academic-cap class="h-6 w-6 text-primary-600" />
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('lecturer.dash_no_quizzes_title') }}</h3>
            <p class="text-sm text-gray-500 mt-2 max-w-sm mx-auto">{{ __('lecturer.dash_no_quizzes_desc') }}</p>
            <div class="mt-4">
                <x-filament::button tag="a" href="{{ \App\Filament\Lecturer\Resources\QuizResource::getUrl('create') }}" size="sm">
                    {{ __('lecturer.dash_create_first_quiz') }}
                </x-filament::button>
            </div>
        </div>
    @endif
</x-filament-widgets::widget>
