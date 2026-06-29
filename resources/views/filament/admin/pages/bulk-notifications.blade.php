<x-filament-panels::page>
    <form wire:submit="send" class="space-y-6 max-w-2xl">
        {{ $this->form }}

        <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
            {{ __('admin.bulk_send') }}
        </x-filament::button>
    </form>
</x-filament-panels::page>
