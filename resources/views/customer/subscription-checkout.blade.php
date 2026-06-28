@extends('layouts.app')
@section('title', 'Subscribe to ' . $plan->name . ' — ' . $platformSettings->app_name)

@push('styles')
<style>
/* sco- extracted inline styles */
.sco-page-wrap     { max-width:520px;margin:48px auto;padding-bottom:64px; }
.sco-back-link     { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);text-decoration:none;margin-bottom:28px; }
.sco-card          { padding:36px; }
.sco-heading       { font-weight:800;color:var(--text-heading);margin-bottom:4px; }
.sco-sub           { color:var(--text-muted);font-size:14px;margin-bottom:28px; }
.sco-billing-grid  { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px; }
.sco-opt-label-title { font-weight:700; }
.sco-opt-price     { font-size:22px;font-weight:800;color:var(--brand-primary); }
.sco-opt-freq      { font-size:12px;color:var(--text-muted); }
.sco-save-badge    { background:var(--success-soft);color:var(--success);font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px; }
.sco-summary-box   { background:#f8f7ff;border-radius:14px;padding:18px;margin-bottom:24px;font-size:14px; }
.sco-summary-title { font-weight:700;margin-bottom:10px;color:var(--text-heading); }
.sco-summary-row   { display:flex;justify-content:space-between;margin-bottom:6px; }
.sco-summary-cycle { display:flex;justify-content:space-between;color:var(--text-muted); }
.sco-summary-total { border-top:1px solid #e5e7eb;margin-top:12px;padding-top:12px;display:flex;justify-content:space-between;font-weight:700;font-size:16px;color:var(--text-heading); }
.sco-pay-btns      { display:flex;flex-direction:column;gap:10px; }
.sco-rzp-btn       { width:100%;justify-content:center; }
.sco-stripe-btn    { width:100%;padding:14px;font-size:15px;font-weight:700;background:#635BFF;color:#fff;
                     border:none;border-radius:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px; }
.sco-secure-note   { text-align:center;font-size:12px;color:var(--text-muted);margin-top:12px; }
</style>
@endpush

@section('content')
<div class="wrap sco-page-wrap">
  <a href="{{ route('pricing') }}" class="sco-back-link">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    {{ __('common.back_to_pricing') }}
  </a>

  <div class="card sco-card">
    <h2 class="sco-heading">{{ __('common.subscribe_to', ['plan' => $plan->name]) }}</h2>
    <p class="sco-sub">{{ __('common.choose_billing_cycle') }}</p>

    <form method="POST" action="{{ route('subscription.initiate', $plan->slug) }}">
      @csrf

      <div class="sco-billing-grid">
        @if($plan->price_monthly > 0)
        <label class="billing-option" id="opt-monthly">
          <input type="radio" name="billing" value="monthly" checked onchange="updateSummary(this.value)">
          <div class="billing-opt-body">
            <div class="sco-opt-label-title">{{ __('common.billing_monthly') }}</div>
            <div class="sco-opt-price">{{ $sym }}{{ number_format($plan->price_monthly, 0) }}</div>
            <div class="sco-opt-freq">{{ __('common.billed_every_month') }}</div>
          </div>
        </label>
        @endif

        @if($plan->price_yearly > 0)
        <label class="billing-option" id="opt-yearly">
          <input type="radio" name="billing" value="yearly" onchange="updateSummary(this.value)">
          <div class="billing-opt-body">
            <div class="sco-opt-label-title">{{ __('common.billing_yearly') }} <span class="sco-save-badge">{{ __('common.save_badge') }}</span></div>
            <div class="sco-opt-price">{{ $sym }}{{ number_format($plan->price_yearly, 0) }}</div>
            <div class="sco-opt-freq">{{ $sym }}{{ number_format($plan->price_yearly / 12, 0) }}/mo {{ __('common.billed_annually_suffix') }}</div>
          </div>
        </label>
        @endif
      </div>

      <div class="sco-summary-box">
        <div class="sco-summary-title">{{ __('common.order_summary') }}</div>
        <div class="sco-summary-row">
          <span>{{ $plan->name }} plan</span>
          <span id="summary-price">{{ $sym }}{{ number_format($plan->price_monthly, 0) }}</span>
        </div>
        <div class="sco-summary-cycle">
          <span>{{ __('common.billing_label') }}</span>
          <span id="summary-billing">{{ __('common.billing_monthly') }}</span>
        </div>
        <div class="sco-summary-total">
          <span>{{ __('common.total') }}</span>
          <span id="summary-total">{{ $sym }}{{ number_format($plan->price_monthly, 0) }}</span>
        </div>
      </div>

      <input type="hidden" name="gateway" id="selected-gateway" value="{{ $hasRazorpay ? 'razorpay' : 'stripe' }}">

      <div class="sco-pay-btns">
        @if($hasRazorpay)
        <button type="submit" onclick="document.getElementById('selected-gateway').value='razorpay'"
          class="btn btn-primary btn-lg sco-rzp-btn">
          {{ __('common.pay_with_razorpay') }}
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        @endif

        @if($hasStripe)
        <button type="submit" onclick="document.getElementById('selected-gateway').value='stripe'"
          class="sco-stripe-btn">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/></svg>
          {{ __('common.pay_with_stripe') }}
        </button>
        @endif
      </div>

      <p class="sco-secure-note">
        {{ __('common.secure_cancel_note') }}
      </p>
    </form>
  </div>
</div>

<style>
.billing-option{display:block;border:2px solid #e5e7eb;border-radius:14px;padding:16px;cursor:pointer;transition:border-color .2s;}
.billing-option:has(input:checked){border-color:var(--brand-primary);background:#fdf8ff;}
.billing-option input{display:none;}
.billing-opt-body{display:flex;flex-direction:column;gap:4px;}
</style>

@push('scripts')
<script>
'use strict';
const monthly     = {{ $plan->price_monthly }};
const yearly      = {{ $plan->price_yearly }};
const sym         = '{{ $sym }}';
const labelMonthly = @json(__('common.billing_monthly'));
const labelYearly  = @json(__('common.billing_yearly'));
function fmt(n){ return sym + Math.round(n).toLocaleString(); }
function updateSummary(billing) {
  const isY = billing === 'yearly';
  document.getElementById('summary-price').textContent   = fmt(isY ? yearly : monthly);
  document.getElementById('summary-billing').textContent = isY ? labelYearly : labelMonthly;
  document.getElementById('summary-total').textContent   = fmt(isY ? yearly : monthly);
}
</script>
@endpush
@endsection
