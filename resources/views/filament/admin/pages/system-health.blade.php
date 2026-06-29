<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">{{ __('admin.system_app_info') }}</x-slot>
            <dl class="space-y-2 text-sm">
                @foreach($appInfo as $key => $value)
                    <div class="flex justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <dt class="text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-white text-right">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('admin.system_server') }}</x-slot>
            <dl class="space-y-2 text-sm">
                @foreach($serverInfo as $key => $value)
                    <div class="flex justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <dt class="text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-filament::section>

        <x-filament::section class="lg:col-span-2">
            <x-slot name="heading">{{ __('admin.system_permissions') }}</x-slot>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($permissions as $path => $writable)
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-2 text-sm">
                        <span class="font-mono text-gray-600 dark:text-gray-300">{{ $path }}</span>
                        <x-filament::badge :color="$writable ? 'success' : 'danger'">
                            {{ $writable ? __('admin.system_writable') : __('admin.system_not_writable') }}
                        </x-filament::badge>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>

    <div class="mt-6">
        <x-filament::button wire:click="clearCache" icon="heroicon-o-trash" color="warning">
            {{ __('admin.system_clear_cache') }}
        </x-filament::button>
    </div>
</x-filament-panels::page>
