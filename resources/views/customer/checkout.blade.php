@extends('layouts.app')
@section('title', __('common.checkout_heading') . ' — ' . $quiz->title)
@push('styles')
<style>
/* chk- extracted inline styles */
.chk-page-wrap    { max-width:600px;margin:60px auto; }
.chk-card         { padding:40px; }
.chk-title        { font-size:28px;font-weight:700;margin-bottom:8px; }
.chk-subtitle     { margin-bottom:32px; }
.chk-summary-card { background:var(--bg-wash);border:1px solid var(--border-light);padding:20px;margin-bottom:32px; }
.chk-summary-row  { display:flex;justify-content:space-between;margin-bottom:12px; }
.chk-summary-total { display:flex;justify-content:space-between;padding-top:12px;border-top:1px solid var(--border-light);font-size:18px;font-weight:700; }
.chk-total-price  { color:var(--brand-primary); }
.chk-alert        { border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;gap:12px;align-items:flex-start; }
.chk-alert-err    { background:var(--danger-soft);border:1px solid var(--danger); }
.chk-alert-info   { background:var(--brand-primary-soft);border:1px solid var(--brand-primary); }
.chk-alert-icon   { flex:none;margin-top:1px; }
.chk-alert-msg-err  { color:var(--danger);font-size:14px;font-weight:600;margin:0; }
.chk-alert-msg-info { color:var(--brand-primary);font-size:14px;font-weight:600;margin:0; }
.chk-no-gw        { background:var(--danger-soft);color:var(--danger);padding:16px;border-radius:12px;text-align:center;font-weight:600; }
.chk-gateway-list { display:flex;flex-direction:column;gap:12px; }
.chk-rzp-btn      { width:100%;padding:16px;font-size:16px; }
.chk-stripe-btn   { width:100%;padding:16px;font-size:16px;background:#635BFF;color:#fff;border-radius:12px;border:none;cursor:pointer;font-weight:600; }
.chk-paypal-btn   { width:100%;padding:16px;font-size:16px;background:#FFC439;color:#003087;border-radius:12px;border:none;cursor:pointer;font-weight:700; }
.chk-footer-note  { text-align:center;margin-top:20px;font-size:13px;color:var(--text-muted); }
</style>
@endpush
@section('content')
<div class="wrap chk-page-wrap">
  <div class="card chk-card">
    <h1 class="chk-title">{{ __('common.checkout_heading') }}</h1>
    <p class="sec chk-subtitle">{{ __('common.checkout_buying_access', ['quiz' => $quiz->title]) }}</p>

    <div class="card chk-summary-card">
      <div class="chk-summary-row">
        <span>{{ __('common.checkout_quiz_access') }}</span>
        <strong>{{ $platformSettings->currency_symbol }}{{ number_format($quiz->price, 2) }}</strong>
      </div>
      <div class="chk-summary-total">
        <span>{{ __('common.checkout_total') }}</span>
        <span class="chk-total-price">{{ $platformSettings->currency_symbol }}{{ number_format($quiz->price, 2) }}</span>
      </div>
    </div>

    {{-- Flash / validation errors --}}
    @if(session('error'))
    <div class="chk-alert chk-alert-err">
      <svg class="chk-alert-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
      <p class="chk-alert-msg-err">{{ session('error') }}</p>
    </div>
    @endif
    @if(session('info'))
    <div class="chk-alert chk-alert-info">
      <svg class="chk-alert-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-primary)" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
      <p class="chk-alert-msg-info">{{ session('info') }}</p>
    </div>
    @endif

    @php
        $hasRazorpay = !empty($platformSettings->razorpay_key) && !empty($platformSettings->razorpay_secret);
        $hasStripe   = !empty($platformSettings->stripe_key)   && !empty($platformSettings->stripe_secret);
        $hasPaypal   = false; // PayPal not yet implemented
        $gatewayCount = (int)$hasRazorpay + (int)$hasStripe;
    @endphp

    @if($gatewayCount === 0)
      <div class="chk-no-gw">
        {{ __('common.checkout_no_gateway') }}
      </div>
    @else
      <div class="chk-gateway-list">
        @if($hasRazorpay)
          <form method="POST" action="{{ route('payment.razorpay.initiate', $quiz->slug) }}">
            @csrf
            <button type="submit" class="btn btn-primary chk-rzp-btn">
              {{ __('common.checkout_pay_razorpay') }}
            </button>
          </form>
        @endif
        @if($hasStripe)
          <form method="POST" action="{{ route('payment.stripe.initiate', $quiz->slug) }}">
            @csrf
            <button type="submit" class="btn chk-stripe-btn">
              {{ __('common.checkout_pay_stripe') }}
            </button>
          </form>
        @endif
        @if($hasPaypal)
          <form method="POST" action="{{ route('payment.paypal.initiate', $quiz->slug) }}">
            @csrf
            <button type="submit" class="btn chk-paypal-btn">
              {{ __('common.checkout_pay_paypal') }}
            </button>
          </form>
        @endif
      </div>
    @endif

    <p class="chk-footer-note">
      {{ __('common.checkout_secure_note') }}
    </p>
  </div>
</div>
@endsection
