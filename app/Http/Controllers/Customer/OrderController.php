<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\PaymentFailedException;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessQuizSaleJob;
use App\Models\Order;
use App\Models\Quiz;
use App\Services\Payment\PaymentService;
use App\Services\Payment\RazorpayService;
use App\Services\Payment\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private PaymentService $payment,
        private RazorpayService $razorpay,
    ) {}

    public function checkout(string $slug)
    {
        $quiz = Quiz::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $user = auth()->user();

        if ($quiz->isFree()) {
            return redirect()->route('quizzes.show', $quiz->slug)->with('error', 'This quiz is free — no purchase needed.');
        }

        if ($user && $user->enrollments()->where('quiz_id', $quiz->id)->exists()) {
            return redirect()->route('quizzes.show', $quiz->slug)->with('info', 'You already have access to this quiz.');
        }

        if (! $this->razorpay->isConfigured() && ! app(StripeService::class)->isConfigured()) {
            return back()->with('error', 'Payment gateway is not configured yet. Please contact the site administrator.');
        }

        return view('customer.checkout', compact('quiz'));
    }

    public function initiateRazorpay(Request $request, string $slug)
    {
        $quiz  = Quiz::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $user  = auth()->user();
        $order = $this->payment->createOrder($user, $quiz, 'razorpay');

        try {
            $rzpData = $this->payment->initiateRazorpay($order);
        } catch (PaymentFailedException $e) {
            return back()->with('error', $e->getMessage());
        }

        return view('customer.payment-razorpay', compact('order', 'quiz', 'rzpData'));
    }

    public function callbackRazorpay(Request $request)
    {
        $request->validate([
            'razorpay_order_id'   => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature'  => 'required',
        ]);

        $valid = $this->razorpay->verifySignature(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature,
        );

        if (! $valid) {
            return redirect()->route('home')->with('error', 'Payment verification failed.');
        }

        // Idempotency: Razorpay retries callbacks up to 3×
        $alreadyProcessed = DB::table('processed_payments')
            ->where('payment_id', $request->razorpay_payment_id)
            ->where('gateway', 'razorpay')
            ->exists();

        if ($alreadyProcessed) {
            $order = Order::where('gateway_order_id', $request->razorpay_order_id)->firstOrFail();
            return redirect()->route('quizzes.show', $order->quiz->slug)
                ->with('success', 'Payment successful! You now have access to the quiz.');
        }

        $order = Order::where('gateway_order_id', $request->razorpay_order_id)->firstOrFail();

        DB::table('processed_payments')->insert([
            'payment_id'   => $request->razorpay_payment_id,
            'gateway'      => 'razorpay',
            'order_id'     => $order->id,
            'processed_at' => now(),
        ]);

        $this->payment->markPaid($order, $request->razorpay_payment_id, $request->all());

        ProcessQuizSaleJob::dispatch($order)->onQueue('payments');

        return redirect()->route('quizzes.show', $order->quiz->slug)
            ->with('success', 'Payment successful! You now have access to the quiz.');
    }

    public function initiateStripe(Request $request, string $slug)
    {
        if (! app(StripeService::class)->isConfigured()) {
            return back()->with('error', 'Stripe payment is not configured yet. Please contact the site administrator.');
        }

        $quiz  = Quiz::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $user  = auth()->user();
        $order = $this->payment->createOrder($user, $quiz, 'stripe');

        try {
            $url = $this->payment->initiateStripe(
                $order,
                route('payment.stripe.success', ['order' => $order->id]),
                route('quiz.checkout', $slug),
            );
            return redirect()->away($url);
        } catch (PaymentFailedException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display-only success page — actual payment processing happens in StripeWebhookController.
     * Stripe redirects here after checkout; we show a confirmation UI.
     */
    public function stripeSuccess(Request $request, Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return view('customer.payment-stripe-success', compact('order'));
    }
}
