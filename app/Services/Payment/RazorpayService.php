<?php

namespace App\Services\Payment;

use App\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    private string $keyId;
    private string $keySecret;
    private string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        $this->keyId     = (string) config('services.razorpay.key', '');
        $this->keySecret = (string) config('services.razorpay.secret', '');
    }

    public function isConfigured(): bool
    {
        return $this->keyId !== '' && $this->keySecret !== '';
    }

    public function createOrder(float $amount, string $currency, string $receiptId): array
    {
        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post("{$this->baseUrl}/orders", [
                'amount'   => (int) ($amount * 100), // paise
                'currency' => $currency,
                'receipt'  => $receiptId,
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            Log::error('Razorpay order creation failed', $body);
            $reason = $body['error']['description'] ?? $body['error']['code'] ?? 'Unknown error';
            throw new PaymentFailedException("Razorpay: {$reason}");
        }

        return $response->json();
    }

    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', "{$orderId}|{$paymentId}", $this->keySecret);
        return hash_equals($expected, $signature);
    }

    public function fetchPayment(string $paymentId): array
    {
        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->get("{$this->baseUrl}/payments/{$paymentId}");

        return $response->json();
    }
}
