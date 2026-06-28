@extends('layouts.app')
@section('title', 'Payment — ' . $quiz->title)
@push('styles')
<style>
.rzp-wrap{max-width:600px;margin:60px auto;text-align:center}
.rzp-card{padding:40px}
.rzp-h2{margin-bottom:8px}
.rzp-note{margin-bottom:32px}
.rzp-summary{border:1px solid var(--border-light);border-radius:12px;padding:20px;margin-bottom:32px;background:var(--bg-wash)}
.rzp-row{display:flex;justify-content:space-between;margin-bottom:8px}
.rzp-row-total{display:flex;justify-content:space-between;font-size:18px;font-weight:700}
.rzp-amount-val{color:var(--brand-primary)}
.rzp-btn{width:100%;padding:16px;font-size:16px}
</style>
@endpush

@section('content')
<div class="wrap rzp-wrap">
  <div class="card rzp-card">
    <h2 class="rzp-h2">Redirecting to payment…</h2>
    <p class="sec rzp-note">Please do not close this window.</p>

    <div class="rzp-summary">
      <div class="rzp-row">
        <span>Quiz</span><strong>{{ $quiz->title }}</strong>
      </div>
      <div class="rzp-row-total">
        <span>Amount</span><span class="rzp-amount-val">{{ $platformSettings->currency_symbol }}{{ number_format($order->amount, 2) }}</span>
      </div>
    </div>

    <button id="rzp-button" class="btn btn-primary rzp-btn">
      Click to Pay {{ $platformSettings->currency_symbol }}{{ number_format($order->amount, 2) }}
    </button>
  </div>
</div>

<form id="rzp-callback" method="POST" action="{{ route('payment.razorpay.callback') }}">
  @csrf
  <input type="hidden" name="razorpay_order_id" id="rzp_order_id">
  <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
  <input type="hidden" name="razorpay_signature" id="rzp_signature">
</form>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
'use strict';
var options = {
  key:       '{{ $rzpData['key'] }}',
  amount:    '{{ $rzpData['amount'] }}',
  currency:  '{{ $rzpData['currency'] }}',
  name:      '{{ $rzpData['name'] }}',
  order_id:  '{{ $rzpData['order_id'] }}',
  prefill:   { name: '{{ $rzpData['prefill']['name'] }}', email: '{{ $rzpData['prefill']['email'] }}' },
  handler: function(response) {
    document.getElementById('rzp_order_id').value   = response.razorpay_order_id;
    document.getElementById('rzp_payment_id').value = response.razorpay_payment_id;
    document.getElementById('rzp_signature').value  = response.razorpay_signature;
    document.getElementById('rzp-callback').submit();
  },
  modal: { ondismiss: function() { window.location = '{{ route('quiz.checkout', $quiz->slug) }}'; } }
};
var rzp = new Razorpay(options);
document.getElementById('rzp-button').onclick = function(e) { rzp.open(); e.preventDefault(); };
window.onload = function() { rzp.open(); };
</script>
@endsection
