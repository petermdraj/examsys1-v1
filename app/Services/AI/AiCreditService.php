<?php

namespace App\Services\AI;

use App\Exceptions\InsufficientAiCreditsException;
use App\Models\AiCreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AiCreditService
{
    /**
     * Atomically consume one generation credit.
     * Returns 'free' or 'wallet' indicating which source was debited.
     * Throws InsufficientAiCreditsException if neither source can cover the cost.
     */
    public function consume(User $user, ?string $referenceId = null): string
    {
        // Try atomic free-credit decrement first
        $decremented = DB::table('users')
            ->where('id', $user->id)
            ->where('ai_credits_free_remaining', '>', 0)
            ->decrement('ai_credits_free_remaining');

        if ($decremented > 0) {
            DB::table('users')->where('id', $user->id)->increment('ai_credits_used');
            $user->refresh();

            AiCreditTransaction::create([
                'user_id'      => $user->id,
                'type'         => 'consumed',
                'amount'       => -1,
                'balance_after' => $user->ai_credits_free_remaining,
                'description'  => 'Free generation used',
                'reference_id' => $referenceId,
            ]);

            return 'free';
        }

        // Fall back to wallet
        $charge = $this->chargePerGeneration($user);

        $walletDecremented = DB::table('users')
            ->where('id', $user->id)
            ->where('wallet_balance', '>=', $charge)
            ->decrement('wallet_balance', $charge);

        if ($walletDecremented === 0) {
            throw new InsufficientAiCreditsException();
        }

        DB::table('users')->where('id', $user->id)->increment('ai_credits_used');
        $user->refresh();

        AiCreditTransaction::create([
            'user_id'      => $user->id,
            'type'         => 'consumed',
            'amount'       => -1,
            'balance_after' => $user->wallet_balance,
            'description'  => "Paid generation — ₹{$charge} deducted",
            'reference_id' => $referenceId,
        ]);

        return 'wallet';
    }

    /**
     * Refund a generation credit to the correct source.
     * Pass the source string returned by consume().
     */
    public function refund(User $user, string $source = 'free', ?string $referenceId = null): void
    {
        if ($source === 'wallet') {
            $charge = $this->chargePerGeneration($user);
            DB::table('users')->where('id', $user->id)->increment('wallet_balance', $charge);
        } else {
            DB::table('users')->where('id', $user->id)->increment('ai_credits_free_remaining');
        }

        DB::table('users')->where('id', $user->id)->decrement('ai_credits_used');
        $user->refresh();

        $balanceAfter = $source === 'wallet' ? $user->wallet_balance : $user->ai_credits_free_remaining;

        AiCreditTransaction::create([
            'user_id'      => $user->id,
            'type'         => 'refunded',
            'amount'       => 1,
            'balance_after' => $balanceAfter,
            'description'  => 'Generation refunded (failed)',
            'reference_id' => $referenceId,
        ]);
    }

    public function addWalletCredits(User $user, float $amount): void
    {
        DB::table('users')->where('id', $user->id)->increment('wallet_balance', $amount);
        $user->refresh();

        AiCreditTransaction::create([
            'user_id'      => $user->id,
            'type'         => 'purchased',
            'amount'       => 0,
            'balance_after' => $user->wallet_balance,
            'description'  => "Wallet top-up ₹{$amount}",
        ]);
    }

    public function adminGrant(User $user, int $credits, string $note = ''): void
    {
        DB::table('users')->where('id', $user->id)->increment('ai_credits_free_remaining', $credits);
        $user->refresh();

        AiCreditTransaction::create([
            'user_id'      => $user->id,
            'type'         => 'admin_grant',
            'amount'       => $credits,
            'balance_after' => $user->ai_credits_free_remaining,
            'description'  => $note ?: "Admin granted {$credits} credits",
        ]);
    }

    private function chargePerGeneration(User $user): float
    {
        $activeSub = $user->activeSubscription()->with('plan')->first();

        if ($activeSub && $activeSub->plan && $activeSub->plan->ai_charge_per_generation !== null) {
            return (float) $activeSub->plan->ai_charge_per_generation;
        }

        return (float) config('quizora.ai_charge_per_gen', 5.00);
    }
}
