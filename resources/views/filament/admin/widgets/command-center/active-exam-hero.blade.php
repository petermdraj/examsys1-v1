<x-filament-widgets::widget class="cc-widget">
    @if($activeQuiz && $liveCount > 0)
        <div class="cc-card overflow-hidden">
            <div class="rounded-xl bg-gradient-to-br from-emerald-800 to-emerald-950 p-6 text-white">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                        <span class="h-2 w-2 rounded-full bg-red-400 animate-pulse"></span>
                        {{ __('admin.cc_monitoring_live') }}
                    </span>
                    <span class="text-xs font-mono text-white/60 bg-white/10 px-2 py-0.5 rounded">#EX-{{ $activeQuiz->id }}</span>
                </div>
                <h3 class="text-xl font-bold truncate">{{ $activeQuiz->title }}</h3>
                <div class="mt-2">
                    @if($activeQuiz->category?->name)
                        <span class="inline-flex text-xs font-medium px-2 py-0.5 rounded-full bg-white/15 text-white/90">
                            {{ $activeQuiz->category->name }}
                        </span>
                    @else
                        <span class="text-white/70 text-sm">{{ __('admin.cc_general') }}</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-4 mt-6">
                    <div>
                        <div class="text-3xl font-bold">{{ $activeQuiz->active_count }}</div>
                        <div class="text-xs uppercase tracking-wide text-white/70">{{ __('admin.cc_active_students') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-amber-300">
                            @if($activeQuiz->duration_minutes)
                                {{ intdiv($activeQuiz->duration_minutes, 60) > 0 ? intdiv($activeQuiz->duration_minutes, 60) . 'h ' : '' }}{{ $activeQuiz->duration_minutes % 60 }}m
                            @else
                                ∞
                            @endif
                        </div>
                        <div class="text-xs uppercase tracking-wide text-white/70">{{ __('admin.cc_duration') }}</div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-white/10">
                    <a href="{{ \App\Filament\Admin\Pages\LiveMonitoring::getUrl() }}" class="text-xs text-white/80 hover:text-white underline">
                        {{ __('admin.nav_live_monitoring') }} →
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="cc-card p-8 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 mb-3">
                <x-heroicon-o-signal-slash class="h-6 w-6 text-gray-400" />
            </div>
            <div class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ __('admin.cc_offline') }}</div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('admin.cc_no_live_exam') }}</h3>
            <p class="text-sm text-gray-500 mt-2 max-w-sm mx-auto">{{ __('admin.cc_no_live_exam_desc') }}</p>
        </div>
    @endif
</x-filament-widgets::widget>
