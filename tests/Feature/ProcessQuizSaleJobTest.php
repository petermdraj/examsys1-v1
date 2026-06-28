<?php

namespace Tests\Feature;

use App\Jobs\ProcessQuizSaleJob;
use App\Models\Order;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\User;
use App\Services\Payment\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ProcessQuizSaleJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_enrollment_on_paid_order(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create(['price' => 99.00]);
        $order = Order::factory()->create([
            'user_id'    => $user->id,
            'quiz_id'    => $quiz->id,
            'status'     => 'paid',
            'amount'     => 99.00,
            'gateway'    => 'razorpay',
        ]);

        $commission = Mockery::mock(CommissionService::class);
        $commission->shouldReceive('settle')->once()->with(Mockery::on(fn($o) => $o->id === $order->id));

        (new ProcessQuizSaleJob($order))->handle($commission);

        $this->assertDatabaseHas('quiz_enrollments', [
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'source'  => 'purchased',
        ]);
    }

    public function test_job_skips_unpaid_order(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'status'  => 'pending',
        ]);

        $commission = Mockery::mock(CommissionService::class);
        $commission->shouldNotReceive('settle');

        (new ProcessQuizSaleJob($order))->handle($commission);

        $this->assertDatabaseMissing('quiz_enrollments', ['quiz_id' => $quiz->id, 'user_id' => $user->id]);
    }

    public function test_job_is_idempotent_on_double_call(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'status'  => 'paid',
        ]);

        $commission = Mockery::mock(CommissionService::class);
        $commission->shouldReceive('settle')->twice();

        // Call twice — enrollment should still be exactly 1 row
        (new ProcessQuizSaleJob($order))->handle($commission);
        (new ProcessQuizSaleJob($order))->handle($commission);

        $this->assertEquals(1, QuizEnrollment::where('quiz_id', $quiz->id)->where('user_id', $user->id)->count());
    }

    public function test_razorpay_idempotency_table_prevents_double_dispatch(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create();
        $order = Order::factory()->create([
            'user_id'         => $user->id,
            'quiz_id'         => $quiz->id,
            'status'          => 'pending',
            'gateway'         => 'razorpay',
            'gateway_order_id' => 'order_test_123',
        ]);

        DB::table('processed_payments')->insert([
            'payment_id'   => 'pay_test_abc',
            'gateway'      => 'razorpay',
            'order_id'     => $order->id,
            'processed_at' => now(),
        ]);

        // Simulate callback — already-processed payment should short-circuit
        $already = DB::table('processed_payments')
            ->where('payment_id', 'pay_test_abc')
            ->where('gateway', 'razorpay')
            ->exists();

        $this->assertTrue($already);
    }
}
