<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessQuizSaleJob;
use App\Models\Order;
use App\Services\Payment\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function __construct(private StripeService $stripe) {}

    public function handle(Request $request)
    {
        $sig = $request->header('Stripe-Signature', '');

        try {
            $event = $this->stripe->verifyWebhook($request->getContent(), $sig);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature invalid', ['error' => $e->getMessage()]);
            return response('Signature invalid', 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response('ok');
        }

        $session = $event->data->object;
        $orderId = $session->metadata->order_id ?? null;

        if (! $orderId) {
            return response('ok');
        }

        $order = Order::find($orderId);

        if (! $order || $order->status === 'paid') {
            return response('ok');
        }

        // Idempotency guard: prevent double-processing if webhook fires twice
        $alreadyProcessed = DB::table('processed_payments')
            ->where('payment_id', $session->payment_intent)
            ->where('gateway', 'stripe')
            ->exists();

        if ($alreadyProcessed) {
            return response('ok');
        }

        DB::table('processed_payments')->insert([
            'payment_id'   => $session->payment_intent,
            'gateway'      => 'stripe',
            'order_id'     => $order->id,
            'processed_at' => now(),
        ]);

        $order->update([
            'status'             => 'paid',
            'gateway_payment_id' => $session->payment_intent,
            'gateway_response'   => (array) $session,
            'paid_at'            => now(),
        ]);

        ProcessQuizSaleJob::dispatch($order)->onQueue('payments');

        return response('ok');
    }
}
