<?php

namespace Tests\Feature;

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

    public function test_consume_is_no_op_and_always_returns_free(): void
    {
        $user = User::factory()->create(['ai_credits_free_remaining' => 0, 'ai_credits_used' => 5]);

        $source = $this->service->consume($user);

        $this->assertEquals('free', $source);
        $this->assertEquals(0, $user->fresh()->ai_credits_free_remaining);
        $this->assertEquals(5, $user->fresh()->ai_credits_used);
    }

    public function test_consume_does_not_deduct_when_credits_available(): void
    {
        $user = User::factory()->create(['ai_credits_free_remaining' => 10, 'ai_credits_used' => 0]);

        $this->service->consume($user);

        $this->assertEquals(10, $user->fresh()->ai_credits_free_remaining);
        $this->assertEquals(0, $user->fresh()->ai_credits_used);
    }

    public function test_refund_is_no_op(): void
    {
        $user = User::factory()->create(['ai_credits_free_remaining' => 0, 'ai_credits_used' => 1]);

        $this->service->refund($user, 'free');

        $this->assertEquals(0, $user->fresh()->ai_credits_free_remaining);
        $this->assertEquals(1, $user->fresh()->ai_credits_used);
    }

    public function test_admin_grant_is_no_op(): void
    {
        $user = User::factory()->create(['ai_credits_free_remaining' => 3]);

        $this->service->adminGrant($user, 5, 'test');

        $this->assertEquals(3, $user->fresh()->ai_credits_free_remaining);
    }
}
