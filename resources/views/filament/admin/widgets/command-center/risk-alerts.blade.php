<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('admin.cc_ai_risk_detection') }}</x-slot>
        <x-slot name="headerEnd">
            @if($count > 0)
                <x-filament::badge color="danger">{{ $count }} {{ __('admin.cc_active_signals') }}</x-filament::badge>
            @else
                <x-filament::badge color="success">{{ __('admin.cc_all_clean') }}</x-filament::badge>
            @endif
        </x-slot>

        <div class="space-y-3">
            @forelse($alerts as $alert)
                <div class="flex gap-3 text-sm">
                    <div class="shrink-0 mt-0.5">
                        @if($alert->level === 'critical')
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-danger-500" />
                        @else
                            <x-heroicon-o-shield-exclamation class="h-5 w-5 text-warning-500" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $alert->title }}</div>
                        <div class="text-gray-500 truncate">{{ \Illuminate\Support\Str::limit($alert->message, 80) }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $alert->quiz_title }}</div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-4">{{ __('admin.cc_no_risks') }}</p>
            @endforelse
        </div>

        @if($count > 0)
            <div class="mt-4">
                <x-filament::button tag="a" href="{{ \App\Filament\Admin\Pages\LiveMonitoring::getUrl() }}" size="sm" color="gray" outlined>
                    {{ __('admin.nav_live_monitoring') }}
                </x-filament::button>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
