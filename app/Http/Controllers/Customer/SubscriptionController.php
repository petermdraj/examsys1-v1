<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\PaymentFailedException;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payment\RazorpayService;
use App\Services\Payment\StripeService;
use App\Settings\PlatformSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct(
        private RazorpayService $razorpay,
        private StripeService $stripe,
    ) {}

    public function checkout(string $slug)
    {
        $plan = Plan::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user = auth()->user();

        abort_unless($user->role === 'creator', 403, __('common.creators_only'));

        if ($plan->price_monthly == 0) {
            return $this->assignFreePlan($plan);
        }

        $hasRazorpay = $this->razorpay->isConfigured();
        $hasStripe   = $this->stripe->isConfigured();

        if (! $hasRazorpay && ! $hasStripe) {
            return back()->with('error', __('common.payment_gateway_not_configured'));
        }

        $billing = request('billing', 'monthly');
        $amount  = $billing === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $sym     = app(PlatformSettings::class)->currency_symbol;

        return view('customer.subscription-checkout', compact('plan', 'billing', 'amount', 'sym', 'hasRazorpay', 'hasStripe'));
    }

    public function initiate(Request $request, string $slug)
    {
        $plan     = Plan::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user     = auth()->user();
        $billing  = $request->input('billing', 'monthly');
        $gateway  = $request->input('gateway', 'razorpay');
        $amount   = $billing === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;
        $sym      = app(PlatformSettings::class)->currency_symbol;
        $currency = strtoupper(app(PlatformSettings::class)->default_currency ?: 'INR');

        abort_unless($user->role === 'creator', 403);

        if ($amount <= 0) {
            return $this->assignFreePlan($plan);
        }

        if ($gateway === 'stripe') {
            if (! $this->stripe->isConfigured()) {
                return back()->with('error', __('common.stripe_not_configured'));
            }
            try {
                $session = $this->stripe->createCheckoutSession(
                    $amount,
                    $currency,
                    "sub_{$plan->slug}_{$billing}_{$user->id}",
                    route('subscription.stripe.success', ['slug' => $plan->slug, 'billing' => $billing]),
                    route('subscription.checkout', $plan->slug),
                );
                return redirect()->away($session['url']);
            } catch (PaymentFailedException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        // Razorpay flow
        if (! $this->razorpay->isConfigured()) {
            return back()->with('error', __('common.razorpay_not_configured'));
        }
        try {
            $receipt  = 'sub_' . substr(md5($plan->id . $user->id . $billing), 0, 36); // max 40 chars
            $rzpOrder = $this->razorpay->createOrder($amount, $currency, $receipt);
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not create payment order: ' . $e->getMessage());
        }

        $rzpData = [
            'key'      => config('services.razorpay.key'),
            'order_id' => $rzpOrder['id'],
            'amount'   => (int) ($amount * 100),
            'currency' => $currency,
            'name'     => config('app.name'),
            'prefill'  => ['name' => $user->name, 'email' => $user->email],
        ];

        return view('customer.subscription-payment', compact('plan', 'billing', 'amount', 'sym', 'rzpData'));
    }

    public function callback(Request $request, string $slug)
    {
        $plan    = Plan::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user    = auth()->user();
        $billing = $request->input('billing', 'monthly');

        $valid = $this->razorpay->verifySignature(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature,
        );

        if (! $valid) {
            return redirect()->route('pricing')->with('error', __('common.payment_verification_failed'));
        }

        $this->activateSubscription($user, $plan, $billing, 'razorpay', $request->razorpay_payment_id);

        return redirect()->route('pricing')->with('success', __('common.subscription_activated', ['plan' => $plan->name]));
    }

    public function stripeSuccess(Request $request, string $slug)
    {
        $plan    = Plan::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user    = auth()->user();
        $billing = $request->input('billing', 'monthly');

        $sessionId = $request->input('session_id');
        if (! $sessionId) {
            return redirect()->route('pricing')->with('error', __('common.payment_session_invalid'));
        }

        try {
            $session = $this->stripe->retrieveSession($sessionId);
        } catch (\Throwable $e) {
            return redirect()->route('pricing')->with('error', __('common.payment_verify_failed'));
        }

        if ($session->payment_status !== 'paid') {
            return redirect()->route('pricing')->with('error', __('common.payment_not_completed'));
        }

        // Idempotency: don't activate twice for the same session
        $alreadyActive = Subscription::where('user_id', $user->id)
            ->where('gateway_subscription_id', $sessionId)
            ->exists();

        if (! $alreadyActive) {
            $this->activateSubscription($user, $plan, $billing, 'stripe', $sessionId);
        }

        return redirect()->route('pricing')->with('success', __('common.subscription_activated', ['plan' => $plan->name]));
    }

    private function activateSubscription($user, Plan $plan, string $billing, string $gateway, string $gatewayRef): void
    {
        DB::transaction(function () use ($user, $plan, $billing, $gateway, $gatewayRef) {
            Subscription::where('user_id', $user->id)->where('status', 'active')->update(['status' => 'cancelled']);

            $period = $billing === 'yearly' ? now()->addYear() : now()->addMonth();
            Subscription::create([
                'user_id'                 => $user->id,
                'plan_id'                 => $plan->id,
                'status'                  => 'active',
                'billing_cycle'           => $billing,
                'current_period_start'    => now(),
                'current_period_end'      => $period,
                'gateway'                 => $gateway,
                'gateway_subscription_id' => $gatewayRef,
            ]);

            $user->update(['ai_credits_free_remaining' => $plan->ai_free_generations]);
        });
    }

    private function assignFreePlan(Plan $plan)
    {
        $user = auth()->user();

        DB::transaction(function () use ($user, $plan) {
            Subscription::where('user_id', $user->id)->where('status', 'active')->update(['status' => 'cancelled']);

            Subscription::create([
                'user_id'              => $user->id,
                'plan_id'              => $plan->id,
                'status'               => 'active',
                'billing_cycle'        => 'monthly',
                'current_period_start' => now(),
                'current_period_end'   => now()->addYear(),
                'gateway'              => 'manual',
            ]);

            $user->update(['ai_credits_free_remaining' => $plan->ai_free_generations]);
        });

        return redirect()->route('pricing')->with('success', __('common.subscription_switched', ['plan' => $plan->name]));
    }
}
