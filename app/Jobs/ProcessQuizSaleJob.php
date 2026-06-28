<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\QuizEnrollment;
use App\Services\Payment\CommissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessQuizSaleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public Order $order) {}

    public function handle(CommissionService $commission): void
    {
        if ($this->order->status !== 'paid') {
            Log::warning("ProcessQuizSaleJob: order {$this->order->id} not paid, skipping");
            return;
        }

        DB::transaction(function () use ($commission) {
            // Lock the order row so concurrent retries queue up
            $order = Order::lockForUpdate()->find($this->order->id);

            QuizEnrollment::firstOrCreate(
                ['quiz_id' => $order->quiz_id, 'user_id' => $order->user_id],
                [
                    'enrolled_at' => now(),
                    'source'      => 'purchased',
                    'order_id'    => $order->id,
                ]
            );

            // Credit creator wallet (idempotent — checks settled_at internally)
            $commission->settle($order);
        });

        // Dispatch notification email outside the transaction — no need to hold the lock
        SendAttemptResultMailJob::dispatch($this->order->user, null, 'purchase_receipt', [
            'order' => $this->order,
            'quiz'  => $this->order->quiz,
        ]);
    }
}
