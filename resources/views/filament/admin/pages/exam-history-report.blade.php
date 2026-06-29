<x-filament-panels::page>
    @if(count($pendingQuizzes) > 0)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/30 p-4">
            <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-200 mb-3">{{ __('admin.pending_results_heading') }}</h3>
            <div class="space-y-2">
                @foreach($pendingQuizzes as $quiz)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-white/80 dark:bg-gray-900/50 px-4 py-3">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $quiz->title }}</div>
                            <div class="text-xs text-gray-500">{{ __('admin.pending_results_count', ['count' => $quiz->completed_count]) }}</div>
                        </div>
                        <x-filament::button
                            size="sm"
                            color="warning"
                            wire:click="publishQuizResults('{{ $quiz->id }}')"
                            wire:confirm="{{ __('admin.pending_results_confirm', ['quiz' => $quiz->title]) }}"
                        >
                            {{ __('admin.pending_results_publish') }}
                        </x-filament::button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
