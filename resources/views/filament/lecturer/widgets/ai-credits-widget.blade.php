<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-sparkles class="w-5 h-5 text-yellow-500" />
                {{ __('lecturer.ai_credits_heading') }}
            </div>
        </x-slot>

        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('lecturer.ai_free_generations') }}</span>
                    <span class="font-bold text-primary-600">{{ $freeCredits }} {{ __('lecturer.ai_credits_left') }}</span>
                </div>
                @php
                    $total = max($freeCredits + $creditsUsed, 1);
                    $pct   = min(100, round(($freeCredits / $total) * 100));
                @endphp
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-500
                        {{ $pct > 50 ? 'bg-green-500' : ($pct > 20 ? 'bg-yellow-500' : 'bg-red-500') }}"
                        style="width: {{ $pct }}%">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $creditsUsed }} {{ __('lecturer.ai_used_lifetime') }}</p>
            </div>

            <div class="pt-1">
                <a href="{{ route('filament.lecturer.pages.ai-generator') }}"
                   class="block text-center text-xs font-semibold py-2 px-3 rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition-colors">
                    {{ __('lecturer.ai_btn_generate_quiz') }}
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
