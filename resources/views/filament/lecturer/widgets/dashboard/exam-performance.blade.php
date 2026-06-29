<x-filament-widgets::widget class="cc-widget">
    <div class="cc-card p-5">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('lecturer.dash_exam_performance') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('lecturer.dash_exam_performance_desc') }}</p>
            </div>
            <x-filament::button tag="a" href="{{ $reportsUrl }}" size="xs" color="gray" outlined>
                {{ __('lecturer.nav_reports') }} →
            </x-filament::button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-gray-500 border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-2 pr-4 font-semibold">{{ __('lecturer.col_title') }}</th>
                        <th class="pb-2 px-3 font-semibold text-center">{{ __('lecturer.col_attempts') }}</th>
                        <th class="pb-2 px-3 font-semibold text-center">{{ __('lecturer.dash_pass_rate') }}</th>
                        <th class="pb-2 pl-3 font-semibold text-right">{{ __('lecturer.dash_avg_duration') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800 last:border-0">
                            <td class="py-3 pr-4">
                                <a href="{{ \App\Filament\Lecturer\Resources\QuizResource::getUrl('edit', ['record' => $row['quiz']]) }}"
                                   class="font-medium text-gray-900 dark:text-white hover:text-primary-600 truncate block max-w-[220px]">
                                    {{ $row['quiz']->title }}
                                </a>
                                <span class="text-xs text-gray-500">{{ __('lecturer.status_' . $row['quiz']->status) }}</span>
                            </td>
                            <td class="py-3 px-3 text-center text-gray-700 dark:text-gray-300">
                                {{ number_format($row['attempts']) }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($row['pass_rate'] !== null)
                                    <span class="font-semibold {{ $row['pass_rate'] >= ($row['quiz']->pass_percentage ?? 60) ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $row['pass_rate'] }}%
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 pl-3 text-right text-gray-700 dark:text-gray-300">
                                @if($row['avg_duration_min'] !== null)
                                    {{ $row['avg_duration_min'] }} {{ __('lecturer.dash_min_abbr') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-sm text-gray-500">
                                {{ __('lecturer.dash_no_exam_performance') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-widgets::widget>
