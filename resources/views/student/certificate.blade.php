@extends('layouts.app')
@section('title', __('common.certificate_of_completion') . ' — ' . config('app.name'))
@push('styles')
<style>
.cert-pg-wrap{max-width:640px;text-align:center}
.cert-pg-card{padding:48px 32px}
.cert-pg-icon{font-size:48px;margin-bottom:16px}
.cert-pg-eyebrow{margin-bottom:8px}
.cert-pg-h1{font-size:28px;margin-bottom:8px}
.cert-pg-quiz{font-size:22px;margin:16px 0;color:var(--brand-primary)}
.cert-pg-date{font-size:14px}
.cert-pg-badge{margin-top:24px;padding:12px;background:var(--success-soft);border-radius:10px;color:var(--success);font-weight:600;font-size:14px}
</style>
@endpush

@section('content')
<div class="wrap cert-pg-wrap">
  <div class="card cert-pg-card">
    <div class="cert-pg-icon">🎓</div>
    <div class="eyebrow cert-pg-eyebrow">{{ __('common.certificate_of_completion') }}</div>
    <h1 class="cert-pg-h1">{{ $cert->user->name }}</h1>
    <p class="sec">{{ __('common.certificate_has_completed') }}</p>
    <h2 class="cert-pg-quiz">{{ $cert->quiz->title }}</h2>
    <p class="muted cert-pg-date">{{ __('common.certificate_issued_on', ['date' => $cert->issued_at->format('d M Y')]) }}</p>
    <div class="cert-pg-badge">
      {{ __('common.certificate_verified_badge') }}
    </div>
  </div>
</div>
@endsection
