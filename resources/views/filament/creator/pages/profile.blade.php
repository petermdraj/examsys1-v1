<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
        <div class="mt-6 flex">
            <x-filament::button type="submit">
                {{ __('creator.profile_save_btn') }}
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
