<x-filament-widgets::widget class="cc-widget">
    <div class="cc-side-stack">
        {{-- Upcoming --}}
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('lecturer.dash_upcoming') }}</h3>
                <x-filament::button tag="a" href="{{ \App\Filament\Lecturer\Resources\QuizResource::getUrl('index') }}" size="xs" color="gray" outlined>
                    {{ __('lecturer.dash_view_all') }}
                </x-filament::button>
            </div>
            <p class="text-xs text-gray-500 mb-4">{{ __('lecturer.dash_upcoming_desc') }}</p>

            <div class="space-y-3">
                @forelse($upcoming as $quiz)
                    @php $isUrgent = $quiz->start_at->isToday() || $quiz->start_at->isTomorrow(); @endphp
                    <div class="flex items-center gap-3 {{ $isUrgent ? 'border-l-2 border-primary-500 pl-3' : '' }}">
                        <div class="shrink-0 text-center w-10">
                            <div class="text-lg font-bold leading-none {{ $isUrgent ? 'text-primary-600' : 'text-gray-700 dark:text-gray-200' }}">
                                {{ $quiz->start_at->format('d') }}
                            </div>
                            <div class="text-[10px] uppercase text-gray-500">{{ $quiz->start_at->format('M') }}</div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-sm truncate">{{ $quiz->title }}</div>
                            <div class="text-xs text-gray-500">{{ $quiz->start_at->format('h:i A') }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-3">{{ __('lecturer.dash_no_upcoming') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Recent quizzes --}}
        <div class="cc-card p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ __('lecturer.recent_quizzes_heading') }}</h3>
            <div class="space-y-3">
                @forelse($recent as $quiz)
                    <a href="{{ \App\Filament\Lecturer\Resources\QuizResource::getUrl('edit', ['record' => $quiz]) }}"
                       class="flex items-center gap-3 group">
                        <div class="shrink-0 flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/20">
                            <x-heroicon-o-book-open class="h-4 w-4 text-gray-500 group-hover:text-primary-600" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-sm truncate text-gray-900 dark:text-white group-hover:text-primary-600">{{ $quiz->title }}</div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-gray-500">{{ $quiz->total_questions }} {{ __('lecturer.col_qs') }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ __('lecturer.status_' . $quiz->status) }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 text-center py-3">{{ __('lecturer.dash_no_recent') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
