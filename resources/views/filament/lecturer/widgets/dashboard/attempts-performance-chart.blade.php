<x-filament-widgets::widget class="cc-widget">
    <div class="cc-card p-5">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('lecturer.dash_perf_analytics') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('lecturer.dash_perf_analytics_desc') }}</p>
            </div>
        </div>

        <div class="flex items-end justify-between gap-2 h-44">
            @foreach($chartData as $data)
                <div class="flex-1 flex flex-col items-center gap-1.5 {{ $data['is_current'] ? 'opacity-100' : 'opacity-75' }}">
                    <div class="flex items-end h-32 w-full justify-center">
                        <div
                            class="w-4 max-w-full rounded-t-md bg-primary-500 dark:bg-primary-400 transition-all"
                            style="height: {{ $data['attempts_height'] }}%"
                            title="{{ $data['full_date'] }}: {{ $data['attempts_val'] }} {{ __('lecturer.col_attempts') }}"
                        ></div>
                    </div>
                    <span class="text-[10px] font-medium {{ $data['is_current'] ? 'text-emerald-700 dark:text-emerald-400 font-bold' : 'text-gray-500' }}">
                        {{ $data['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
