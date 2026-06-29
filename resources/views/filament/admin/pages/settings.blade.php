<x-filament-panels::page>
    <x-filament-panels::form
        id="form"
        wire:submit="save"
    >
        {{ $this->form }}

        <div class="settings-form-actions mt-8 border-t border-gray-200 pt-6 dark:border-white/10">
            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :alignment="\Filament\Support\Enums\Alignment::End"
            />
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
