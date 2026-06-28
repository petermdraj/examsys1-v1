<?php

namespace App\Filament\Creator\Pages;

use App\Models\AiCreditTransaction;
use App\Models\CreatorPayout;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Earnings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    public static function getNavigationLabel(): string { return __('creator.nav_earnings'); }
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.creator.pages.earnings';
    public function getTitle(): string { return __('creator.nav_earnings'); }

    public function getViewData(): array
    {
        $user   = auth()->user();
        $userId = $user->id;

        $baseQuery = Order::whereHas('quiz', fn($q) => $q->where('creator_id', $userId))
            ->where('status', 'paid');

        $orders = (clone $baseQuery)->with('quiz')->latest('paid_at')->take(20)->get();

        $payouts = CreatorPayout::where('creator_id', $userId)->latest()->take(10)->get();

        $totalEarned  = (clone $baseQuery)->sum('creator_earning');
        $totalPaidOut = CreatorPayout::where('creator_id', $userId)->where('status', 'paid')->sum('amount');
        // wallet_balance is the single source of truth: earnings credited in, AI purchases and paid-out payouts debited out
        $pending      = max(0, (float) $user->wallet_balance);
        $totalSales   = (clone $baseQuery)->count();

        $thisMonth        = (clone $baseQuery)->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('creator_earning');
        $lastMonth        = (clone $baseQuery)->whereMonth('paid_at', now()->subMonth()->month)->whereYear('paid_at', now()->subMonth()->year)->sum('creator_earning');
        $monthGrowth      = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : null;

        $tokensRemaining   = (int) ($user->ai_credits_free_remaining ?? 0);
        $monthlyAllocation = optional($user->activeSubscription?->plan)->ai_free_generations ?? 0;

        return compact(
            'orders', 'payouts',
            'totalEarned', 'totalPaidOut', 'pending', 'totalSales',
            'thisMonth', 'lastMonth', 'monthGrowth',
            'tokensRemaining', 'monthlyAllocation'
        );
    }

    /** Resolve per-token charge and plan name for the logged-in creator. */
    private function tokenRate(): array
    {
        $user = auth()->user();
        $plan = $user->activeSubscription()?->with('plan')->first()?->plan ?? null;

        $rate     = $plan?->ai_charge_per_generation !== null
            ? (float) $plan->ai_charge_per_generation
            : (float) app(\App\Settings\PlatformSettings::class)->ai_charge_per_gen_default;
        $planName = $plan?->name ?? 'Platform default';

        return [$rate, $planName];
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $sym  = app(\App\Settings\PlatformSettings::class)->currency_symbol;

        return [
            Action::make('topup_tokens')
                ->label(__('creator.earn_get_more_tokens'))
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->modalHeading(__('creator.earnings_modal_topup'))
                ->modalDescription(__('creator.earnings_modal_topup_desc'))
                ->modalWidth('md')
                ->form(function () use ($user, $sym): array {
                    [$rate, $planName] = $this->tokenRate();
                    $walletBalance = (float) $user->wallet_balance;

                    return [
                        Forms\Components\Placeholder::make('plan_info')
                            ->label(__('creator.earnings_plan_label'))
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div style="display:flex;flex-wrap:wrap;gap:16px;padding:12px 16px;background:#f5f3ff;border-radius:10px;font-size:13px;">'
                                . '<span><strong>Plan:</strong> ' . e($planName) . '</span>'
                                . '<span><strong>Rate:</strong> ' . e($sym) . number_format($rate, 2) . ' per token</span>'
                                . '<span><strong>Wallet balance:</strong> ' . e($sym) . number_format($walletBalance, 2) . '</span>'
                                . '</div>'
                            )),

                        Forms\Components\TextInput::make('tokens')
                            ->label(__('creator.earnings_field_tokens'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(10000)
                            ->default(10)
                            ->live(debounce: 400)
                            ->suffix(__('creator.earnings_tokens_suffix'))
                            ->helperText(__('creator.earnings_tokens_helper')),

                        Forms\Components\Placeholder::make('cost_preview')
                            ->label(__('creator.earnings_cost_summary'))
                            ->content(function (Forms\Get $get) use ($rate, $sym, $walletBalance): \Illuminate\Support\HtmlString {
                                $tokens = max(0, (int) $get('tokens'));
                                $cost   = round($tokens * $rate, 2);
                                $after  = round($walletBalance - $cost, 2);
                                $enough = $walletBalance >= $cost;

                                $color     = $enough ? '#166534' : '#991B1B';
                                $bgColor   = $enough ? '#f0fdf4' : '#fef2f2';
                                $afterNote = $enough
                                    ? 'Wallet after purchase: <strong>' . e($sym) . number_format($after, 2) . '</strong>'
                                    : '<strong>Insufficient wallet balance.</strong> Top up your wallet first.';

                                return new \Illuminate\Support\HtmlString(
                                    '<div style="padding:12px 16px;border-radius:10px;background:' . $bgColor . ';font-size:13px;color:' . $color . ';">'
                                    . '<div style="display:flex;justify-content:space-between;margin-bottom:6px;">'
                                    . '<span>' . $tokens . ' tokens × ' . e($sym) . number_format($rate, 2) . '</span>'
                                    . '<strong>' . e($sym) . number_format($cost, 2) . '</strong>'
                                    . '</div>'
                                    . '<div>' . $afterNote . '</div>'
                                    . '</div>'
                                );
                            })
                            ->live(),
                    ];
                })
                ->action(function (array $data) use ($sym) {
                    $tokens = max(1, (int) $data['tokens']);
                    $user   = auth()->user()->fresh();

                    [$rate] = $this->tokenRate();
                    $cost   = round($tokens * $rate, 2);

                    if ((float) $user->wallet_balance < $cost) {
                        Notification::make()
                            ->title(__('creator.earnings_insufficient_err'))
                            ->body(__('creator.earnings_insufficient_body', ['sym' => $sym, 'cost' => number_format($cost, 2), 'balance' => number_format($user->wallet_balance, 2)]))
                            ->danger()
                            ->send();
                        return;
                    }

                    \Illuminate\Support\Facades\DB::transaction(function () use ($user, $tokens, $cost, $sym) {
                        \Illuminate\Support\Facades\DB::table('users')
                            ->where('id', $user->id)
                            ->decrement('wallet_balance', $cost);

                        \Illuminate\Support\Facades\DB::table('users')
                            ->where('id', $user->id)
                            ->increment('ai_credits_free_remaining', $tokens);

                        $user->refresh();

                        AiCreditTransaction::create([
                            'user_id'       => $user->id,
                            'type'          => 'purchased',
                            'amount'        => $tokens,
                            'balance_after' => $user->ai_credits_free_remaining,
                            'description'   => "Token top-up: +{$tokens} tokens ({$sym}" . number_format($cost, 2) . " deducted from wallet)",
                        ]);
                    });

                    Notification::make()
                        ->title(__('creator.earnings_tokens_added'))
                        ->body(__('creator.earnings_tokens_added_body', ['count' => $tokens, 'sym' => $sym, 'cost' => number_format($cost, 2)]))
                        ->success()
                        ->send();
                }),

            Action::make('request_payout')
                ->label(__('creator.payout_request_btn'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form(function () use ($sym): array {
                    $user        = auth()->user()->fresh();
                    $pending     = max(0, round((float) $user->wallet_balance, 2));
                    $canWithdraw = $pending >= 100;

                    return [
                        Forms\Components\Placeholder::make('balance_info')
                            ->label(__('creator.payout_available'))
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-radius:10px;background:' . ($canWithdraw ? '#f0fdf4' : '#fef9c3') . ';font-size:13px;">'
                                . '<div>'
                                . '<p style="font-size:22px;font-weight:800;color:' . ($canWithdraw ? '#15803d' : '#92400e') . ';line-height:1;">'
                                . e($sym) . number_format($pending, 2)
                                . '</p>'
                                . '<p style="color:#6b7280;margin-top:2px;">Earnings minus previously paid-out amounts</p>'
                                . '</div>'
                                . ($canWithdraw
                                    ? '<span style="font-size:11px;font-weight:600;color:#15803d;background:#dcfce7;padding:4px 10px;border-radius:20px;">Eligible</span>'
                                    : '<span style="font-size:11px;font-weight:600;color:#92400e;background:#fef3c7;padding:4px 10px;border-radius:20px;">Min. ' . e($sym) . '100 required</span>')
                                . '</div>'
                            )),

                        Forms\Components\TextInput::make('amount')
                            ->label(__('creator.payout_field_amount'))
                            ->numeric()
                            ->required()
                            ->minValue(100)
                            ->maxValue($pending)
                            ->prefix($sym)
                            ->default(fn () => $pending >= 100 ? $pending : null)
                            ->helperText("Minimum {$sym}100 · Maximum {$sym}" . number_format($pending, 2)),

                        Forms\Components\TextInput::make('gateway')
                            ->label(__('creator.payout_field_gateway'))
                            ->placeholder(__('creator.payout_gateway_placeholder'))
                            ->required(),

                        Forms\Components\Textarea::make('note')
                            ->label(__('creator.payout_field_note'))
                            ->rows(2),
                    ];
                })
                ->action(function (array $data) use ($sym) {
                    $user    = auth()->user()->fresh();
                    $pending = max(0, (float) $user->wallet_balance);

                    if ((float) $data['amount'] > $pending) {
                        Notification::make()
                            ->title(__('creator.payout_exceeds_balance'))
                            ->body(__('creator.payout_exceeds_body', ['sym' => $sym, 'amount' => number_format($pending, 2)]))
                            ->danger()->send();
                        return;
                    }

                    CreatorPayout::create([
                        'creator_id'   => $user->id,
                        'amount'       => $data['amount'],
                        'gateway'      => $data['gateway'],
                        'note'         => $data['note'] ?? null,
                        'status'       => 'pending',
                        'requested_at' => now(),
                    ]);
                    Notification::make()->title(__('creator.payout_requested'))->body(__('creator.payout_processing_time'))->success()->send();
                }),
        ];
    }
}
