<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('admin.cc_upcoming_exams') }}</x-slot>
        <x-slot name="description">{{ __('admin.cc_next_scheduled') }}</x-slot>

        <div class="space-y-3">
            @forelse($quizzes as $quiz)
                @php
                    $isUrgent = $quiz->start_at->isToday() || $quiz->start_at->isTomorrow();
                @endphp
                <div class="flex items-center gap-3 {{ $isUrgent ? 'border-l-2 border-primary-500 pl-3' : '' }}">
                    <div class="shrink-0 text-center w-10">
                        <div class="text-lg font-bold leading-none {{ $isUrgent ? 'text-primary-600' : 'text-gray-700 dark:text-gray-200' }}">
                            {{ $quiz->start_at->format('d') }}
                        </div>
                        <div class="text-[10px] uppercase text-gray-500">
                            {{ $quiz->start_at->isToday() ? __('admin.cc_today') : ($quiz->start_at->isTomorrow() ? __('admin.cc_tomorrow') : $quiz->start_at->format('M')) }}
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-semibold text-sm truncate text-gray-900 dark:text-white">{{ $quiz->title }}</div>
                        <div class="text-xs text-gray-500">{{ $quiz->category?->name }} · {{ $quiz->start_at->format('h:i A') }}</div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-4">{{ __('admin.cc_no_upcoming') }}</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
