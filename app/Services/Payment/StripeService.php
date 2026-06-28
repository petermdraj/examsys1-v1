<?php

namespace App\Services\Payment;

use App\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeService
{
    private ?StripeClient $stripe = null;

    public function isConfigured(): bool
    {
        return (string) config('services.stripe.secret', '') !== '';
    }

    private function client(): StripeClient
    {
        if (! $this->stripe) {
            $secret = (string) config('services.stripe.secret', '');
            if ($secret === '') {
                throw new PaymentFailedException('Stripe is not configured. Please add STRIPE_SECRET to your .env file.');
            }
            $this->stripe = new StripeClient($secret);
        }
        return $this->stripe;
    }

    public function createCheckoutSession(float $amount, string $currency, string $orderId, string $successUrl, string $cancelUrl): array
    {
        try {
            $session = $this->client()->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => strtolower($currency),
                        'unit_amount'  => (int) ($amount * 100),
                        'product_data' => ['name' => 'Quiz Purchase'],
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => $cancelUrl,
                'metadata'    => ['order_id' => $orderId],
            ]);

            return ['session_id' => $session->id, 'url' => $session->url];
        } catch (ApiErrorException $e) {
            Log::error('Stripe checkout failed', ['error' => $e->getMessage()]);
            throw new PaymentFailedException($e->getMessage());
        }
    }

    public function verifyWebhook(string $payload, string $sigHeader): object
    {
        return \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret', '')
        );
    }

    public function retrieveSession(string $sessionId): object
    {
        return $this->client()->checkout->sessions->retrieve($sessionId);
    }
}
