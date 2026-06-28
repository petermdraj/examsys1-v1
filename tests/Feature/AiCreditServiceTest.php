<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientAiCreditsException;
use App\Models\User;
use App\Services\AI\AiCreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCreditServiceTest extends TestCase
{
    use RefreshDatabase;

    private AiCreditService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AiCreditService::class);
    }

    public function test_consume_free_credit_atomically(): void
    {
        $user = User::factory()->create(['ai_credits_free_remaining' => 1, 'ai_credits_used' => 0]);

        $source = $this->service->consume($user);

        $this->assertEquals('free', $source);
        $this->assertEquals(0, $user->fresh()->ai_credits_free_remaining);
        $this->assertEquals(1, $user->fresh()->ai_credits_used);
    }

    public function test_consume_throws_when_no_free_or_wallet(): void
    {
        $user = User::factory()->create(['ai_credits_free_remaining' => 0, 'wallet_balance' => 0.00]);

        $this->expectException(InsufficientAiCreditsException::class);
        $this->service->consume($user);
    }

    public function test_concurrent_consume_only_once_atomically(): void
    {
        $user = User::factory()->create(['ai_credits_free_remaining' => 1, 'wallet_balance' => 0]);

        // Simulate two concurrent consume calls — second should throw
        $this->service->consume($user);
        $user->refresh();

        $this->expectException(InsufficientAiCreditsException::class);
        $this->service->consume($user);
    }

    public function test_consume_wallet_when_no_free_credits(): void
    {
        config(['quizora.ai_charge_per_gen' => 5.00]);
        $user = User::factory()->create(['ai_credits_free_remaining' => 0, 'wallet_balance' => 10.00]);

        $source = $this->service->consume($user);

        $this->assertEquals('wallet', $source);
        $this->assertEquals(5.00, $user->fresh()->wallet_balance);
    }

    public function test_refund_free_returns_to_free_remaining(): void
    {
        $user = User::factory()->create(['ai_credits_free_remaining' => 0, 'ai_credits_used' => 1]);

        $this->service->refund($user, 'free');

        $this->assertEquals(1, $user->fresh()->ai_credits_free_remaining);
        $this->assertEquals(0, $user->fresh()->ai_credits_used);
    }

    public function test_refund_wallet_returns_to_wallet(): void
    {
        config(['quizora.ai_charge_per_gen' => 5.00]);
        $user = User::factory()->create(['ai_credits_free_remaining' => 0, 'wallet_balance' => 5.00, 'ai_credits_used' => 1]);

        $this->service->refund($user, 'wallet');

        $this->assertEquals(10.00, $user->fresh()->wallet_balance);
        $this->assertEquals(0, $user->fresh()->ai_credits_used);
    }

    public function test_balance_after_is_accurate_post_decrement(): void
    {
        $user = User::factory()->create(['ai_credits_free_remaining' => 3]);
        $this->service->consume($user);

        $tx = $user->aiCreditTransactions()->latest()->first();
        $this->assertEquals(2, $tx->balance_after);
    }
}
