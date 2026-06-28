<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Quiz;
use App\Settings\PlatformSettings;

class CommissionService
{
    public function calculate(Quiz $quiz, float $amount): array
    {
        // Use the creator's active subscription plan commission rate if available,
        // otherwise fall back to the platform default setting.
        $creator = $quiz->creator()->with('activeSubscription.plan')->first();
        $planRate = $creator?->activeSubscription?->plan?->commission_rate;
        $commissionRate = $planRate !== null
            ? (float) $planRate
            : (float) app(PlatformSettings::class)->platform_commission_default;

        $commission     = round($amount * $commissionRate / 100, 2);
        $creatorEarning = round($amount - $commission, 2);

        return [
            'platform_commission' => $commission,
            'creator_earning'     => $creatorEarning,
            'commission_rate'     => $commissionRate,
        ];
    }

    public function settle(Order $order): void
    {
        // Idempotency guard: only credit if this order hasn't already been settled.
        // Re-read inside the caller's transaction to benefit from the row lock.
        if ($order->settled_at !== null) {
            return;
        }

        $order->update(['settled_at' => now()]);
        $order->quiz->creator->increment('wallet_balance', $order->creator_earning);
    }
}
