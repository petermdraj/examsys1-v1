<x-filament-widgets::widget class="cc-widget">
    <div class="cc-side-stack">
        {{-- Risk alerts --}}
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('admin.cc_ai_risk_detection') }}</h3>
                @if($count > 0)
                    <x-filament::badge color="danger">{{ $count }} {{ __('admin.cc_active_signals') }}</x-filament::badge>
                @else
                    <x-filament::badge color="success">{{ __('admin.cc_all_clean') }}</x-filament::badge>
                @endif
            </div>

            <div class="space-y-3">
                @forelse($alerts as $alert)
                    <div class="flex gap-3 text-sm">
                        <div class="shrink-0 mt-0.5">
                            @if($alert->level === 'critical')
                                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-danger-500" />
                            @else
                                <x-heroicon-o-shield-exclamation class="h-5 w-5 text-warning-500" />
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $alert->title }}</div>
                            <div class="text-gray-500 truncate">{{ \Illuminate\Support\Str::limit($alert->message, 80) }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $alert->quiz_title }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">{{ __('admin.cc_no_risks') }}</p>
                @endforelse
            </div>

            @if($count > 0)
                <div class="mt-4">
                    <x-filament::button tag="a" href="{{ \App\Filament\Admin\Pages\LiveMonitoring::getUrl() }}" size="sm" color="gray" outlined>
                        {{ __('admin.nav_live_monitoring') }}
                    </x-filament::button>
                </div>
            @endif
        </div>

        {{-- Upcoming exams --}}
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('admin.cc_upcoming_exams') }}</h3>
                <x-filament::button
                    tag="a"
                    href="{{ \App\Filament\Admin\Resources\QuizResource::getUrl('index') }}"
                    size="xs"
                    color="gray"
                    outlined
                >
                    {{ __('admin.cc_view_all_exams') }}
                </x-filament::button>
            </div>
            <p class="text-xs text-gray-500 mb-4">{{ __('admin.cc_next_scheduled') }}</p>

            <div class="space-y-3">
                @forelse($quizzes as $quiz)
                    @php
                        $isUrgent = $quiz->start_at->isToday() || $quiz->start_at->isTomorrow();
                    @endphp
                    <div class="flex items-center gap-3 {{ $isUrgent ? 'border-l-2 border-primary-500 pl-3' : '' }}">
                        <div class="shrink-0 flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700">
                            <x-heroicon-o-book-open class="h-4 w-4 text-gray-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-sm truncate text-gray-900 dark:text-white">{{ $quiz->title }}</div>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($quiz->category?->name)
                                    <span class="inline-flex text-[10px] font-medium uppercase tracking-wide px-1.5 py-0.5 rounded bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                                        {{ $quiz->category->name }}
                                    </span>
                                @endif
                                <span class="text-xs text-gray-500">{{ $quiz->start_at->format('h:i A') }}</span>
                            </div>
                        </div>
                        <div class="shrink-0 text-center w-10">
                            <div class="text-lg font-bold leading-none {{ $isUrgent ? 'text-primary-600' : 'text-gray-700 dark:text-gray-200' }}">
                                {{ $quiz->start_at->format('d') }}
                            </div>
                            <div class="text-[10px] uppercase text-gray-500">
                                {{ $quiz->start_at->isToday() ? __('admin.cc_today') : ($quiz->start_at->isTomorrow() ? __('admin.cc_tomorrow') : $quiz->start_at->format('M')) }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">{{ __('admin.cc_no_upcoming') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
