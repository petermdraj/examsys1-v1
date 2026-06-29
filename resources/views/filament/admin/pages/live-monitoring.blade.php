<x-filament-panels::page wire:poll.5s="refreshSessions">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.live_header_desc') }}</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 dark:border-gray-700 px-3 py-1 text-xs">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ __('admin.live_connected') }}</span>
                @if($lastRefreshed)
                    <span class="text-gray-400">· {{ $lastRefreshed }}</span>
                @endif
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach([
                ['key' => 'active_users', 'label' => __('admin.live_kpi_active'), 'color' => 'text-primary-600', 'icon' => 'heroicon-o-user-group'],
                ['key' => 'critical_risk', 'label' => __('admin.live_kpi_critical'), 'color' => 'text-danger-600', 'icon' => 'heroicon-o-exclamation-triangle'],
                ['key' => 'paused', 'label' => __('admin.live_kpi_paused'), 'color' => 'text-warning-600', 'icon' => 'heroicon-o-pause'],
                ['key' => 'completed_today', 'label' => __('admin.live_kpi_finished'), 'color' => 'text-success-600', 'icon' => 'heroicon-o-check-circle'],
            ] as $kpi)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $kpis[$kpi['key']] ?? 0 }}</div>
                        @svg($kpi['icon'], 'h-6 w-6 ' . $kpi['color'])
                    </div>
                    <div class="text-xs font-medium text-gray-500 mt-1 uppercase tracking-wide">{{ $kpi['label'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">{{ __('admin.live_filter_quiz') }}</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterQuizId">
                        <option value="all">{{ __('admin.live_filter_all') }}</option>
                        @foreach($this->quizzes as $quiz)
                            <option value="{{ $quiz->id }}">{{ $quiz->title }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">{{ __('admin.live_filter_risk') }}</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterRisk">
                        <option value="all">{{ __('admin.live_filter_all') }}</option>
                        <option value="low">{{ __('admin.risk_low') }}</option>
                        <option value="warning">{{ __('admin.risk_warning') }}</option>
                        <option value="critical">{{ __('admin.risk_critical') }}</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">{{ __('admin.live_filter_status') }}</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterStatus">
                        <option value="all">{{ __('admin.live_filter_all') }}</option>
                        <option value="in_progress">{{ __('admin.live_status_active') }}</option>
                        <option value="paused">{{ __('admin.live_status_paused') }}</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">{{ __('admin.live_search') }}</label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        wire:model.live.debounce.400ms="search"
                        type="search"
                        placeholder="{{ __('admin.live_search_placeholder') }}"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        {{-- Sessions table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('admin.live_col_student') }}</th>
                            <th class="px-4 py-3">{{ __('admin.live_col_quiz') }}</th>
                            <th class="px-4 py-3">{{ __('admin.live_col_progress') }}</th>
                            <th class="px-4 py-3">{{ __('admin.live_col_risk') }}</th>
                            <th class="px-4 py-3">{{ __('admin.live_col_status') }}</th>
                            <th class="px-4 py-3">{{ __('admin.live_col_activity') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('admin.live_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($sessions as $session)
                            <tr wire:key="session-{{ $session->id }}" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $session->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $session->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 max-w-[200px] truncate">{{ $session->quiz->title }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 flex-1 max-w-20 rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div class="h-1.5 rounded-full bg-primary-500" style="width: {{ $session->progressPercentage() }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $session->progressPercentage() }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $riskColor = match($session->risk_level) {
                                            'critical' => 'danger',
                                            'warning' => 'warning',
                                            default => 'success',
                                        };
                                    @endphp
                                    <x-filament::badge :color="$riskColor">{{ ucfirst($session->risk_level) }} ({{ $session->risk_score }})</x-filament::badge>
                                </td>
                                <td class="px-4 py-3">
                                    <x-filament::badge :color="$session->status === 'paused' ? 'warning' : 'primary'">
                                        {{ $session->status === 'paused' ? __('admin.live_status_paused') : __('admin.live_status_active') }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ ($session->last_activity_at ?? $session->started_at)?->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-1 flex-wrap">
                                        @if($session->status === 'in_progress')
                                            <x-filament::button size="xs" color="warning" wire:click="pauseAttempt('{{ $session->id }}')" wire:confirm="{{ __('admin.live_confirm_pause') }}">
                                                {{ __('admin.live_action_pause') }}
                                            </x-filament::button>
                                            <x-filament::button size="xs" color="danger" wire:click="terminateAttempt('{{ $session->id }}')" wire:confirm="{{ __('admin.live_confirm_terminate') }}">
                                                {{ __('admin.live_action_terminate') }}
                                            </x-filament::button>
                                        @elseif($session->status === 'paused')
                                            <x-filament::button size="xs" color="success" wire:click="resumeAttempt('{{ $session->id }}')">
                                                {{ __('admin.live_action_resume') }}
                                            </x-filament::button>
                                            <x-filament::button size="xs" color="danger" wire:click="terminateAttempt('{{ $session->id }}')" wire:confirm="{{ __('admin.live_confirm_terminate') }}">
                                                {{ __('admin.live_action_terminate') }}
                                            </x-filament::button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                    {{ __('admin.live_no_sessions') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
