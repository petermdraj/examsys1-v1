<x-filament-panels::page>
    @php $quiz = $this->getQuiz(); @endphp
    <div class="space-y-4">
        <div class="flex items-center gap-4 p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
            <div>
                <p class="text-sm text-gray-500">{{ __('lecturer.stat_questions') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $quiz->total_questions }}</p>
            </div>
            <div class="border-l pl-4">
                <p class="text-sm text-gray-500">{{ __('lecturer.stat_total_marks') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $quiz->total_marks }}</p>
            </div>
            <div class="border-l pl-4">
                <p class="text-sm text-gray-500">{{ __('lecturer.col_status') }}</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $quiz->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ ucfirst($quiz->status) }}
                </span>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
