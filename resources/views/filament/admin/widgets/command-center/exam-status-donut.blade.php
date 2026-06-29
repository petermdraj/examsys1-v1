<x-filament-widgets::widget class="cc-widget">
    <div class="cc-card p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ __('admin.cc_exam_status') }}</h3>

        <div class="cc-donut" style="background: {{ $breakdown['gradient'] }}">
            <div class="cc-donut-hole">
                <span class="cc-donut-pct">{{ $breakdown['published_pct'] }}%</span>
                <span class="cc-donut-label">{{ __('admin.cc_published') }}</span>
            </div>
        </div>

        <div class="mt-5 space-y-2.5">
            <div class="flex items-center justify-between text-sm">
                <span class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300">
                    <span class="cc-legend-dot" style="background:#2E9E68"></span>
                    {{ __('admin.cc_published') }}
                </span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $breakdown['published'] }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300">
                    <span class="cc-legend-dot" style="background:#6366f1"></span>
                    {{ __('admin.cc_scheduled') }}
                </span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $breakdown['scheduled'] }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300">
                    <span class="cc-legend-dot" style="background:#9ca3af"></span>
                    {{ __('admin.cc_draft') }}
                </span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $breakdown['draft'] }}</span>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
