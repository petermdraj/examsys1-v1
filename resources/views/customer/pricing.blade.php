@extends('layouts.app')
@section('title', __('common.nav_pricing') . ' — ' . $platformSettings->app_name)

@section('content')
<div class="wrap prc-wrap-padded">

  <div class="prc-hero">
    <div class="eyebrow prc-eyebrow">{{ __('common.pricing_eyebrow') }}</div>
    <h1 class="prc-h1">
      {{ __('common.pricing_title') }}
    </h1>
    <p class="prc-subtitle">
      {{ __('common.pricing_subtitle') }}
    </p>
  </div>

  {{-- Billing toggle --}}
  <div class="prc-billing-toggle">
    <span id="lbl-monthly" class="prc-billing-lbl" style="color:var(--brand-primary);">{{ __('common.pricing_billing_monthly') }}</span>
    <label class="toggle-switch">
      <input type="checkbox" id="billing-toggle" onchange="switchBilling(this.checked)">
      <span class="toggle-knob"></span>
    </label>
    <span id="lbl-yearly" class="prc-billing-lbl" style="color:var(--text-muted);">
      {{ __('common.pricing_billing_yearly') }} <span class="prc-save-badge">{{ __('common.pricing_save_badge') }}</span>
    </span>
  </div>

  {{-- Plan cards --}}
  <div class="pricing-grid">
    @foreach($plans as $plan)
      @php
        $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
        $isPopular = $plan->slug === 'pro';
        $monthlyPrice = (float) $plan->price_monthly;
        $yearlyPrice  = (float) $plan->price_yearly;
        $isFree       = $monthlyPrice === 0.0;
      @endphp
      <div class="pricing-card {{ $isPopular ? 'pricing-card--popular' : '' }} {{ $isCurrent ? 'pricing-card--current' : '' }}">
        @if($isPopular)
          <div class="pricing-badge">{{ __('common.pricing_most_popular') }}</div>
        @endif
        @if($isCurrent)
          <div class="pricing-badge pricing-badge--current">{{ __('common.pricing_your_plan') }}</div>
        @endif

        <div class="pricing-name">{{ $plan->name }}</div>

        {{-- Monthly price --}}
        <div class="pricing-price" data-monthly="{{ $monthlyPrice }}" data-yearly="{{ $yearlyPrice }}">
          @if($isFree)
            <span class="pricing-amount">{{ __('common.pricing_free') }}</span>
          @else
            <span class="pricing-currency">{{ $platformSettings->currency_symbol }}</span>
            <span class="pricing-amount" data-monthly="{{ number_format($monthlyPrice, 0) }}" data-yearly="{{ number_format($yearlyPrice, 0) }}">{{ number_format($monthlyPrice, 0) }}</span>
            <span class="pricing-period" data-monthly="{{ __('common.pricing_per_month') }}" data-yearly="{{ __('common.pricing_per_year') }}">{{ __('common.pricing_per_month') }}</span>
          @endif
        </div>

        @if(!$isFree && $yearlyPrice > 0)
          <div class="pricing-yearly-note" data-monthly="" data-yearly="{{ $platformSettings->currency_symbol }}{{ number_format($yearlyPrice / 12, 0) }}/mo billed yearly" style="font-size:12px;color:var(--success);font-weight:600;min-height:18px;margin-bottom:8px;"></div>
        @else
          <div class="prc-yearly-placeholder"></div>
        @endif

        {{-- Key limits --}}
        <div class="pricing-meta">
          <span>{{ __('common.pricing_free_ai_generations', ['count' => $plan->ai_free_generations]) }}</span>
          @if($plan->max_published_quizzes)
            <span>{{ __('common.pricing_up_to_quizzes', ['count' => $plan->max_published_quizzes]) }}</span>
          @else
            <span>{{ __('common.pricing_unlimited_quizzes') }}</span>
          @endif
          <span>{{ __('common.pricing_commission_rate', ['rate' => $plan->commission_rate]) }}</span>
        </div>

        {{-- Feature list --}}
        @if($plan->features && count($plan->features) > 0)
          <ul class="pricing-features">
            @foreach($plan->features as $feature)
              <li>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M20 6L9 17l-5-5"/></svg>
                {{ $feature }}
              </li>
            @endforeach
          </ul>
        @endif

        {{-- CTA --}}
        @if($isCurrent)
          <div class="btn btn-outline prc-cta-disabled">{{ __('common.pricing_current_plan_btn') }}</div>
        @elseif($isFree)
          @auth
            @if(auth()->user()->role === 'creator')
              <a href="{{ route('subscription.checkout', $plan->slug) }}" class="btn btn-outline prc-btn-full">{{ __('common.pricing_switch_free') }}</a>
            @else
              <a href="{{ route('register') }}" class="btn btn-outline prc-btn-full">{{ __('common.pricing_get_started_free') }}</a>
            @endif
          @else
            <a href="{{ route('register') }}" class="btn btn-outline prc-btn-full">{{ __('common.pricing_get_started_free') }}</a>
          @endauth
        @else
          @auth
            @if(auth()->user()->role === 'creator')
              <a href="{{ route('subscription.checkout', $plan->slug) }}" class="btn {{ $isPopular ? 'btn-accent' : 'btn-primary' }} prc-btn-full" id="cta-{{ $plan->slug }}">
                {{ __('common.pricing_upgrade_to', ['name' => $plan->name]) }}
              </a>
            @else
              <a href="{{ route('register') }}?role=creator" class="btn {{ $isPopular ? 'btn-accent' : 'btn-primary' }} prc-btn-full">
                {{ __('common.pricing_become_creator') }}
              </a>
            @endif
          @else
            <a href="{{ route('register') }}?role=creator" class="btn {{ $isPopular ? 'btn-accent' : 'btn-primary' }} prc-btn-full" id="cta-{{ $plan->slug }}">
              {{ __('common.pricing_get_plan', ['name' => $plan->name]) }}
            </a>
          @endauth
        @endif
      </div>
    @endforeach
  </div>

  {{-- FAQ / Reassurance strip --}}
  <div class="prc-reassurance">
    <div class="prc-reassure-item">
      <div class="prc-reassure-icon">🔒</div>
      <div class="prc-reassure-title">{{ __('common.pricing_secure_payments') }}</div>
      <div class="prc-reassure-desc">{{ __('common.pricing_secure_payments_desc') }}</div>
    </div>
    <div class="prc-reassure-item">
      <div class="prc-reassure-icon">🔄</div>
      <div class="prc-reassure-title">{{ __('common.pricing_cancel_anytime') }}</div>
      <div class="prc-reassure-desc">{{ __('common.pricing_cancel_anytime_desc') }}</div>
    </div>
    <div class="prc-reassure-item">
      <div class="prc-reassure-icon">💬</div>
      <div class="prc-reassure-title">{{ __('common.pricing_need_help') }}</div>
      <div class="prc-reassure-desc">{{ __('common.pricing_need_help_desc') }}</div>
    </div>
  </div>

</div>

<style>
/* Pricing page utility classes */
.prc-wrap-padded{padding-top:48px;padding-bottom:64px}
.prc-hero{text-align:center;margin-bottom:48px}
.prc-eyebrow{margin-bottom:12px}
.prc-h1{font-size:clamp(28px,4vw,44px);font-weight:800;color:var(--text-heading);margin-bottom:16px}
.prc-subtitle{color:var(--text-muted);font-size:17px;max-width:480px;margin:0 auto}
.prc-billing-toggle{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:40px}
.prc-billing-lbl{font-weight:600;font-size:14px}
.prc-save-badge{background:var(--success-soft);color:var(--success);font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:4px}
.prc-yearly-placeholder{min-height:26px;margin-bottom:8px}
.prc-btn-full{width:100%;text-align:center}
.prc-cta-disabled{width:100%;text-align:center;cursor:default;opacity:.6}
.prc-reassurance{margin-top:64px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:28px;max-width:900px;margin-left:auto;margin-right:auto}
.prc-reassure-item{text-align:center}
.prc-reassure-icon{font-size:28px;margin-bottom:8px}
.prc-reassure-title{font-weight:700;color:var(--text-heading);margin-bottom:4px}
.prc-reassure-desc{font-size:13px;color:var(--text-muted)}
.pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;max-width:1000px;margin:0 auto;}
.pricing-card{background:#fff;border:2px solid #e5e7eb;border-radius:20px;padding:32px 28px;position:relative;display:flex;flex-direction:column;gap:0;transition:border-color .2s,box-shadow .2s;}
.pricing-card--popular{border-color:var(--brand-accent);box-shadow:0 8px 32px rgba(224,164,49,.18);}
.pricing-card--current{border-color:var(--brand-primary);box-shadow:0 4px 16px rgba(108,46,99,.12);}
.pricing-badge{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:var(--brand-accent);color:#fff;font-size:11px;font-weight:700;padding:3px 14px;border-radius:20px;white-space:nowrap;letter-spacing:.04em;text-transform:uppercase;}
.pricing-badge--current{background:var(--brand-primary);}
.pricing-name{font-size:20px;font-weight:800;color:var(--text-heading);margin-bottom:16px;}
.pricing-price{display:flex;align-items:baseline;gap:4px;margin-bottom:4px;}
.pricing-currency{font-size:22px;font-weight:700;color:var(--text-heading);}
.pricing-amount{font-size:48px;font-weight:800;line-height:1;color:var(--text-heading);}
.pricing-period{font-size:15px;color:var(--text-muted);margin-left:2px;}
.pricing-meta{display:flex;flex-direction:column;gap:6px;margin:16px 0;padding:16px;background:#f8f7ff;border-radius:12px;font-size:13px;color:var(--text-muted);}
.pricing-meta span::before{content:'→ ';color:var(--brand-primary);}
.pricing-features{list-style:none;padding:0;margin:0 0 24px;display:flex;flex-direction:column;gap:10px;flex:1;}
.pricing-features li{display:flex;align-items:flex-start;gap:8px;font-size:14px;color:var(--text-body);}
.pricing-features li svg{flex:none;margin-top:2px;color:var(--success);}
.toggle-switch{position:relative;display:inline-block;width:44px;height:24px;}
.toggle-switch input{opacity:0;width:0;height:0;}
.toggle-knob{position:absolute;inset:0;background:#e5e7eb;border-radius:24px;cursor:pointer;transition:.25s;}
.toggle-knob::before{content:'';position:absolute;width:18px;height:18px;border-radius:50%;background:#fff;left:3px;top:3px;transition:.25s;box-shadow:0 1px 4px rgba(0,0,0,.18);}
input:checked + .toggle-knob{background:var(--brand-primary);}
input:checked + .toggle-knob::before{transform:translateX(20px);}
</style>

@push('scripts')
<script>
'use strict';
function switchBilling(yearly) {
  document.getElementById('lbl-monthly').style.color = yearly ? 'var(--text-muted)' : 'var(--brand-primary)';
  document.getElementById('lbl-yearly').style.color  = yearly ? 'var(--brand-primary)' : 'var(--text-muted)';

  document.querySelectorAll('.pricing-amount[data-monthly]').forEach(el => {
    el.textContent = yearly ? el.dataset.yearly : el.dataset.monthly;
  });
  document.querySelectorAll('.pricing-period[data-monthly]').forEach(el => {
    el.textContent = yearly ? el.dataset.yearly : el.dataset.monthly;
  });
  document.querySelectorAll('.pricing-yearly-note[data-yearly]').forEach(el => {
    el.textContent = yearly ? el.dataset.yearly : '';
  });

  // Update CTA links to carry billing param
  document.querySelectorAll('a[href*="subscription/checkout"], a[href*="subscription.checkout"]').forEach(a => {
    const url = new URL(a.href);
    url.searchParams.set('billing', yearly ? 'yearly' : 'monthly');
    a.href = url.toString();
  });
}
</script>
@endpush
@endsection
