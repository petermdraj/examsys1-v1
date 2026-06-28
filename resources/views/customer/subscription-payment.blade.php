@extends('layouts.app')
@section('title', 'Complete payment — ' . $platformSettings->app_name)

@push('styles')
<style>
.subpay-wrap{max-width:440px;margin:48px auto;padding-bottom:64px}
.subpay-card{padding:36px;text-align:center}
.subpay-icon{font-size:40px;margin-bottom:16px}
.subpay-h2{font-weight:800;color:var(--text-heading);margin-bottom:8px}
.subpay-desc{color:var(--text-muted);font-size:14px;margin-bottom:28px}
.subpay-btn{width:100%}
.subpay-cancel{display:block;margin-top:12px;font-size:13px;color:var(--text-muted)}
</style>
@endpush

@section('content')
<div class="wrap subpay-wrap">
  <div class="card subpay-card">
    <div class="subpay-icon">💳</div>
    <h2 class="subpay-h2">{{ __('common.complete_your_payment') }}</h2>
    <p class="subpay-desc">
      {{ $plan->name }} plan · {{ ucfirst($billing) }} · {{ $sym }}{{ number_format($amount, 0) }}
    </p>

    <button id="pay-btn" onclick="openRazorpay()" class="btn btn-primary btn-lg subpay-btn">
      Pay {{ $sym }}{{ number_format($amount, 0) }} with Razorpay
    </button>
    <a href="{{ route('pricing') }}" class="subpay-cancel">{{ __('common.cancel') }}</a>
  </div>
</div>

<form id="rzp-callback" method="POST" action="{{ route('subscription.callback', $plan->slug) }}">
  @csrf
  <input type="hidden" name="billing" value="{{ $billing }}">
  <input type="hidden" name="razorpay_order_id" id="rzp_order_id">
  <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
  <input type="hidden" name="razorpay_signature" id="rzp_signature">
</form>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@push('scripts')
<script>
'use strict';
function openRazorpay() {
  const options = {
    key:         '{{ $rzpData['key'] }}',
    amount:      {{ $rzpData['amount'] }},
    currency:    '{{ $rzpData['currency'] }}',
    order_id:    '{{ $rzpData['order_id'] }}',
    name:        '{{ $rzpData['name'] }}',
    description: '{{ $plan->name }} plan ({{ $billing }})',
    prefill:     { name: '{{ $rzpData['prefill']['name'] }}', email: '{{ $rzpData['prefill']['email'] }}' },
    theme:       { color: '{{ $platformSettings->primary_color }}' },
    handler: function(resp) {
      document.getElementById('rzp_order_id').value   = resp.razorpay_order_id;
      document.getElementById('rzp_payment_id').value = resp.razorpay_payment_id;
      document.getElementById('rzp_signature').value  = resp.razorpay_signature;
      document.getElementById('rzp-callback').submit();
    },
    modal: { ondismiss: function() { document.getElementById('pay-btn').disabled = false; } },
  };
  document.getElementById('pay-btn').disabled = true;
  new Razorpay(options).open();
}
window.onload = () => openRazorpay();
</script>
@endpush
@endsection
