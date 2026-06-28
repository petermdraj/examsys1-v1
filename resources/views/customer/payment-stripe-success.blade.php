@extends('layouts.app')
@section('title', 'Payment received — Quizora')
@push('styles')
<style>
.stripe-wrap{max-width:520px;margin:80px auto;text-align:center}
.stripe-icon-circle{width:64px;height:64px;background:var(--success-soft,#f0fdf4);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px}
.stripe-h1{font-size:28px;margin:0 0 12px}
.stripe-desc{color:var(--text-muted);margin:0 0 32px}
.stripe-processing{color:var(--text-muted);font-size:14px;margin-bottom:20px}
</style>
@endpush

@section('content')
<div class="wrap stripe-wrap">
  <div class="stripe-icon-circle">
    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5"><path d="M5 12l5 5L20 7"/></svg>
  </div>
  <h1 class="stripe-h1">Payment received!</h1>
  <p class="stripe-desc">
    Your payment is confirmed. Access is being activated — this usually takes a few seconds.
    You'll be able to start the quiz once enrollment is complete.
  </p>

  @if($order->status === 'paid')
    <a href="{{ route('quizzes.show', $order->quiz->slug) }}" class="btn btn-accent">Start quiz →</a>
  @else
    <p class="stripe-processing">Still processing…</p>
    <a href="{{ route('quizzes.show', $order->quiz->slug) }}" class="btn btn-ghost">View quiz</a>
    <script>
'use strict';
      // Poll for enrollment every 3s for up to 30s
      let polls = 0;
      const interval = setInterval(() => {
        polls++;
        fetch(location.href).then(r => r.text()).then(html => {
          if (html.includes('Start quiz') || polls >= 10) {
            clearInterval(interval);
            location.reload();
          }
        }).catch(() => {});
      }, 3000);
    </script>
  @endif
</div>
@endsection
