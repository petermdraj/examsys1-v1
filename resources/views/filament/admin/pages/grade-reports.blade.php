<x-filament-panels::page>
    <div class="space-y-6 max-w-4xl">
        {{ $this->form }}

        @php($previewRows = $this->getPreviewRows())

        @if(($data['quiz_id'] ?? null) && count($previewRows) > 0)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ __('admin.grade_preview_heading') }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('admin.grade_preview_sub') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase text-gray-500 border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">{{ __('admin.grade_col_student') }}</th>
                                <th class="px-4 py-2">{{ __('admin.grade_col_batch') }}</th>
                                <th class="px-4 py-2">{{ __('admin.grade_col_percentage') }}</th>
                                <th class="px-4 py-2">{{ __('admin.grade_col_result') }}</th>
                                <th class="px-4 py-2">{{ __('admin.grade_col_submitted') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewRows as $row)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-4 py-2">{{ $row['rank'] }}</td>
                                    <td class="px-4 py-2">
                                        <div class="font-medium">{{ $row['student'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $row['email'] }}</div>
                                    </td>
                                    <td class="px-4 py-2">{{ $row['batch'] }}</td>
                                    <td class="px-4 py-2">{{ $row['percentage'] }}%</td>
                                    <td class="px-4 py-2">
                                        <span class="{{ $row['is_passed'] ? 'text-green-600' : 'text-red-600' }} font-medium">
                                            {{ $row['result'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-500">
                                        {{ $row['submitted_at']?->format('d M Y, H:i') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($data['quiz_id'] ?? null)
            <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center text-gray-500">
                {{ __('admin.grade_no_data') }}
            </div>
        @endif
    </div>
</x-filament-panels::page>
