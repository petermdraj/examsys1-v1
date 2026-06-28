<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Quiz;
use App\Models\User;

class PaymentService
{
    public function __construct(
        private RazorpayService $razorpay,
        private StripeService $stripe,
        private CommissionService $commission,
    ) {}

    public function initiateRazorpay(Order $order): array
    {
        $rzpOrder = $this->razorpay->createOrder(
            $order->amount,
            $order->currency,
            $order->id,
        );

        $order->update(['gateway_order_id' => $rzpOrder['id']]);

        return [
            'gateway'    => 'razorpay',
            'key'        => config('services.razorpay.key'),
            'order_id'   => $rzpOrder['id'],
            'amount'     => (int) ($order->amount * 100),
            'currency'   => $order->currency,
            'name'       => config('app.name'),
            'prefill'    => ['name' => $order->user->name, 'email' => $order->user->email],
        ];
    }

    public function initiateStripe(Order $order, string $successUrl, string $cancelUrl): string
    {
        $session = $this->stripe->createCheckoutSession(
            $order->amount,
            $order->currency,
            $order->id,
            $successUrl,
            $cancelUrl,
        );

        $order->update(['gateway_order_id' => $session['session_id']]);

        return $session['url'];
    }

    public function createOrder(User $user, Quiz $quiz, string $gateway = 'razorpay'): Order
    {
        $split = $this->commission->calculate($quiz, $quiz->price);

        return Order::create([
            'user_id'             => $user->id,
            'quiz_id'             => $quiz->id,
            'amount'              => $quiz->price,
            'currency'            => strtoupper(app(\App\Settings\PlatformSettings::class)->default_currency ?: 'INR'),
            'platform_commission' => $split['platform_commission'],
            'creator_earning'     => $split['creator_earning'],
            'status'              => 'pending',
            'gateway'             => $gateway,
        ]);
    }

    public function markPaid(Order $order, string $paymentId, array $gatewayResponse = []): void
    {
        $order->update([
            'status'             => 'paid',
            'gateway_payment_id' => $paymentId,
            'gateway_response'   => $gatewayResponse,
            'paid_at'            => now(),
        ]);
    }
}
