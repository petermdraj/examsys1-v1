<x-filament-widgets::widget class="cc-widget">
    <div class="cc-card p-5">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('admin.cc_perf_analytics') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('admin.cc_perf_analytics_desc') }}</p>
            </div>
            <div class="flex gap-4 text-xs text-gray-500 shrink-0">
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded bg-gray-300 dark:bg-gray-600"></span>
                    {{ __('admin.cc_attendance') }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded bg-emerald-600"></span>
                    {{ __('admin.cc_pass_rate') }}
                </span>
            </div>
        </div>

        <div class="flex items-end justify-between gap-1 h-44">
            @foreach($chartData as $data)
                <div class="flex-1 flex flex-col items-center gap-1.5 {{ $data['is_current'] ? 'opacity-100' : 'opacity-75' }}">
                    <div class="flex items-end gap-1 h-32 w-full justify-center">
                        <div
                            class="w-2.5 rounded-t-md bg-gray-300 dark:bg-gray-600 transition-all"
                            style="height: {{ $data['attendance_height'] }}%"
                            title="{{ $data['full_date'] }}: {{ $data['attendance_val'] }} {{ __('admin.cc_attendance') }}"
                        ></div>
                        <div
                            class="w-2.5 rounded-t-md bg-emerald-600 transition-all"
                            style="height: {{ $data['pass_height'] }}%"
                            title="{{ $data['full_date'] }}: {{ $data['pass_val'] }}%"
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
