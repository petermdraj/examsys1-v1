<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-sparkles class="w-5 h-5 text-yellow-500" />
                {{ __('creator.ai_credits_wallet_heading') }}
            </div>
        </x-slot>

        <div class="space-y-4">
            {{-- Plan badge --}}
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('creator.ai_current_plan') }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                    {{ $planName }}
                </span>
            </div>

            {{-- Free credits bar --}}
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('creator.ai_free_generations') }}</span>
                    <span class="font-bold text-primary-600">{{ $freeCredits }} {{ __('creator.ai_credits_left') }}</span>
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
                <p class="text-xs text-gray-400 mt-1">{{ $creditsUsed }} {{ __('creator.ai_used_lifetime') }}</p>
            </div>

            {{-- Wallet --}}
            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-wallet class="w-5 h-5 text-yellow-500" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('creator.ai_wallet_balance') }}</span>
                </div>
                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $walletBalance }}</span>
            </div>

            {{-- CTA --}}
            <div class="flex gap-2 pt-1">
                <a href="{{ route('filament.creator.pages.ai-generator') }}"
                   class="flex-1 text-center text-xs font-semibold py-2 px-3 rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition-colors">
                    {{ __('creator.ai_btn_generate_quiz') }}
                </a>
                <a href="{{ route('pricing') }}"
                   class="flex-1 text-center text-xs font-semibold py-2 px-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    {{ __('creator.ai_btn_upgrade_plan') }}
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
