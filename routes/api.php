<?php

use App\Http\Controllers\Api\AiGenerateController;
use App\Http\Controllers\Api\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Stripe webhook — no auth, no CSRF (API routes are CSRF-exempt by default)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// SSE AI generate route lives in web.php (needs session-based auth)
