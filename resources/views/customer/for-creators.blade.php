@extends('layouts.app')
@section('title', __('common.nav_for_creators') . ' — ' . $platformSettings->app_name)
@section('meta_description', __('quiz.fc_hero_sub'))

@push('styles')
<style>
.fc-pos-rel{position:relative}
.fc-link-primary{color:var(--brand-primary);font-weight:600}
.fc-wrap-pt{padding-top:80px}
.fc-cta-mt{margin-top:64px}
.fc-cta-btn{position:relative;flex:none}
.fc-pricing-note{text-align:center;margin-top:16px;font-size:13px;color:var(--text-muted)}
.fc-acc-icon{color:var(--brand-accent)}
.fc-progress-27{width:27%}
</style>
@endpush

@section('content')

{{-- HERO --}}
<div class="hero-dark">
  <div class="wrap">
    <div class="hero-dark-grid">
      <div class="hero-dark-text">
        <div class="hero-eyebrow">{{ __('quiz.fc_eyebrow') }}</div>
        <h1 class="hero-dark-h1">{{ __('quiz.fc_hero_h1') }}</h1>
        <p class="hero-dark-sub">{{ __('quiz.fc_hero_sub') }}</p>
        <div class="hero-dark-actions">
          <a href="{{ route('register') }}?role=creator" class="btn btn-accent btn-lg">
            {{ __('quiz.fc_hero_cta_primary') }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
          <a href="{{ route('pricing') }}" class="btn btn-ghost-light btn-lg">{{ __('quiz.fc_hero_cta_secondary') }}</a>
        </div>
      </div>

      {{-- AI Generator mockup --}}
      <div class="ai-gen-mockup" aria-hidden="true">
        <div class="ai-gen-header">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="fc-acc-icon"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z"/></svg>
          {{ __('quiz.fc_ai_generator_title') }}
        </div>
        <div class="ai-gen-prompt">
          <div class="ai-gen-prompt-label">{{ __('quiz.fc_ai_prompt_label') }}</div>
          <div class="ai-gen-prompt-text">PHP OOP concepts for intermediate developers · 15 MCQs · negative 0.25</div>
        </div>
        <div class="ai-gen-stream">
          <div class="ai-gen-q ai-gen-q--done">
            <span class="ai-gen-qnum">Q1</span>
            Which keyword prevents a PHP class from being extended?
            <span class="ai-gen-check">✓</span>
          </div>
          <div class="ai-gen-q ai-gen-q--done">
            <span class="ai-gen-qnum">Q2</span>
            What does the <code>abstract</code> keyword enforce?
            <span class="ai-gen-check">✓</span>
          </div>
          <div class="ai-gen-q ai-gen-q--streaming">
            <span class="ai-gen-qnum">Q3</span>
            How does late static binding differ from<span class="ai-cursor"></span>
          </div>
          <div class="ai-gen-shimmer"></div>
          <div class="ai-gen-shimmer ai-gen-shimmer--short"></div>
        </div>
        <div class="ai-gen-footer">
          <span class="ai-gen-badge">{{ __('quiz.fc_ai_generated_badge') }}</span>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- SOCIAL PROOF --}}
@php
  $s1v = $platformSettings->homepage_stat_1_value ?? '';
  $s1l = $platformSettings->homepage_stat_1_label ?? '';
  $s2v = $platformSettings->homepage_stat_2_value ?? '';
  $s2l = $platformSettings->homepage_stat_2_label ?? '';
  $s3v = $platformSettings->homepage_stat_3_value ?? '';
  $s3l = $platformSettings->homepage_stat_3_label ?? '';
  $showStats = $s1v || $s2v || $s3v;
@endphp
@if($showStats)
  <div class="wrap">
    <div class="stats-strip stats-strip--fc">
      @if($s1v)<div class="stat-item"><div class="stat-value">{{ $s1v }}</div>@if($s1l)<div class="stat-label">{{ $s1l }}</div>@endif</div>@endif
      @if($s2v)<div class="stat-item"><div class="stat-value">{{ $s2v }}</div>@if($s2l)<div class="stat-label">{{ $s2l }}</div>@endif</div>@endif
      @if($s3v)<div class="stat-item"><div class="stat-value">{{ $s3v }}</div>@if($s3l)<div class="stat-label">{{ $s3l }}</div>@endif</div>@endif
    </div>
  </div>
@endif

{{-- FEATURE ROWS --}}
<div class="wrap fc-wrap-pt">

  {{-- Feature 1: AI Generation --}}
  <div class="feature-row">
    <div class="feature-row-text">
      <div class="feature-row-label">{{ __('quiz.fc_feat1_label') }}</div>
      <h2 class="feature-row-h2">{{ __('quiz.fc_feat1_h2') }}</h2>
      <p class="feature-row-body">{{ __('quiz.fc_feat1_body') }}</p>
      <ul class="feature-row-bullets">
        <li>{{ __('quiz.fc_feat1_b1') }}</li>
        <li>{{ __('quiz.fc_feat1_b2') }}</li>
        <li>{{ __('quiz.fc_feat1_b3') }}</li>
        <li>{{ __('quiz.fc_feat1_b4') }}</li>
      </ul>
    </div>
    <div class="feature-row-visual">
      <div class="fv-ai-gen">
        <div class="fv-ai-header">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="var(--brand-accent)"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z"/></svg>
          {{ __('quiz.fc_generating_questions') }}
        </div>
        <div class="fv-ai-q">
          <div class="fv-qnum">Q4</div>
          <div class="fv-qtext">What is the output of <code>var_dump(0 == "a")</code> in PHP 8?</div>
          <div class="fv-opts">
            <div class="fv-opt">bool(true)</div>
            <div class="fv-opt fv-opt--correct">bool(false)</div>
            <div class="fv-opt">int(0)</div>
            <div class="fv-opt">NULL</div>
          </div>
        </div>
        <div class="fv-ai-progress">
          <div class="fv-progress-bar fc-progress-27"></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Feature 2: IBPS Exam Interface (flipped) --}}
  <div class="feature-row feature-row--flip">
    <div class="feature-row-text">
      <div class="feature-row-label">{{ __('quiz.fc_feat2_label') }}</div>
      <h2 class="feature-row-h2">{{ __('quiz.fc_feat2_h2') }}</h2>
      <p class="feature-row-body">{{ __('quiz.fc_feat2_body') }}</p>
      <ul class="feature-row-bullets">
        <li>{{ __('quiz.fc_feat2_b1') }}</li>
        <li>{{ __('quiz.fc_feat2_b2') }}</li>
        <li>{{ __('quiz.fc_feat2_b3') }}</li>
        <li>{{ __('quiz.fc_feat2_b4') }}</li>
      </ul>
    </div>
    <div class="feature-row-visual">
      <div class="fv-ibps">
        <div class="fv-ibps-header">
          <span>PHP OOP Quiz</span>
          <span class="fv-ibps-timer">⏱ 38:42</span>
        </div>
        <div class="fv-ibps-q">Which keyword allows a child class to call the parent method?</div>
        <div class="fv-ibps-opts">
          <div class="fv-ibps-opt">self::</div>
          <div class="fv-ibps-opt fv-ibps-opt--selected">parent::</div>
          <div class="fv-ibps-opt">static::</div>
          <div class="fv-ibps-opt">$this-></div>
        </div>
        <div class="fv-ibps-palette">
          @for($i = 1; $i <= 20; $i++)
            <span class="fv-pal-box
              @if(in_array($i, [1,2,3,4,6,7,9,12])) fv-pal--answered
              @elseif(in_array($i, [5,8])) fv-pal--visited
              @elseif($i === 10) fv-pal--review
              @elseif($i === 7) fv-pal--current
              @else fv-pal--unvisited
              @endif
            ">{{ $i }}</span>
          @endfor
        </div>
      </div>
    </div>
  </div>

  {{-- Feature 3: Earn --}}
  <div class="feature-row">
    <div class="feature-row-text">
      <div class="feature-row-label">{{ __('quiz.fc_feat3_label') }}</div>
      <h2 class="feature-row-h2">{{ __('quiz.fc_feat3_h2') }}</h2>
      <p class="feature-row-body">{{ __('quiz.fc_feat3_body') }}</p>
      <ul class="feature-row-bullets">
        <li>{{ __('quiz.fc_feat3_b1') }}</li>
        <li>{{ __('quiz.fc_feat3_b2') }}</li>
        <li>{{ __('quiz.fc_feat3_b3') }}</li>
        <li>{{ __('quiz.fc_feat3_b4') }}</li>
      </ul>
    </div>
    <div class="feature-row-visual">
      <div class="fv-earnings">
        <div class="fv-earn-header">{{ __('quiz.fc_earnings_this_month') }}</div>
        <div class="fv-earn-amount">{{ $sym }}12,480</div>
        <div class="fv-earn-chart">
          @foreach([40,65,45,80,55,90,72,85,60,95,70,88] as $h)
            <div class="fv-bar" style="height:{{ $h }}%;"></div>
          @endforeach
        </div>
        <div class="fv-earn-stats">
          <div><div class="fv-earn-stat-val">142</div><div class="fv-earn-stat-lab">{{ __('quiz.fc_stat_attempts') }}</div></div>
          <div><div class="fv-earn-stat-val">{{ $sym }}87.9</div><div class="fv-earn-stat-lab">{{ __('quiz.fc_stat_avg_sale') }}</div></div>
          <div><div class="fv-earn-stat-val">68%</div><div class="fv-earn-stat-lab">{{ __('quiz.fc_stat_pass_rate') }}</div></div>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- COMPACT FEATURE LIST --}}
<div class="wrap">
  <div class="compact-features">
    <div class="cf-header">{{ __('quiz.fc_compact_features_header') }}</div>
    <div class="cf-grid">
      <div class="cf-item"><span class="cf-check">✓</span> {{ __('quiz.fc_cf_item1') }}</div>
      <div class="cf-item"><span class="cf-check">✓</span> {{ __('quiz.fc_cf_item2') }}</div>
      <div class="cf-item"><span class="cf-check">✓</span> {{ __('quiz.fc_cf_item3') }}</div>
      <div class="cf-item"><span class="cf-check">✓</span> {{ __('quiz.fc_cf_item4') }}</div>
      <div class="cf-item"><span class="cf-check">✓</span> {{ __('quiz.fc_cf_item5') }}</div>
      <div class="cf-item"><span class="cf-check">✓</span> {{ __('quiz.fc_cf_item6') }}</div>
      <div class="cf-item"><span class="cf-check">✓</span> {{ __('quiz.fc_cf_item7') }}</div>
      <div class="cf-item"><span class="cf-check">✓</span> {{ __('quiz.fc_cf_item8') }}</div>
    </div>
  </div>
</div>

{{-- HOW IT WORKS --}}
<div class="wrap">
  <div class="fc-hiw">
    <h2 class="fc-hiw-title">{{ __('quiz.fc_hiw_title') }}</h2>
    <div class="fc-hiw-steps">
      <div class="fc-step">
        <div class="fc-step-num">1</div>
        <div class="fc-step-title">{{ __('quiz.fc_hiw_step1_title') }}</div>
        <div class="fc-step-desc">{{ __('quiz.fc_hiw_step1_desc') }}</div>
      </div>
      <div class="fc-step-arrow">→</div>
      <div class="fc-step">
        <div class="fc-step-num">2</div>
        <div class="fc-step-title">{{ __('quiz.fc_hiw_step2_title') }}</div>
        <div class="fc-step-desc">{{ __('quiz.fc_hiw_step2_desc') }}</div>
      </div>
      <div class="fc-step-arrow">→</div>
      <div class="fc-step">
        <div class="fc-step-num">3</div>
        <div class="fc-step-title">{{ __('quiz.fc_hiw_step3_title') }}</div>
        <div class="fc-step-desc">{{ __('quiz.fc_hiw_step3_desc') }}</div>
      </div>
      <div class="fc-step-arrow">→</div>
      <div class="fc-step">
        <div class="fc-step-num">4</div>
        <div class="fc-step-title">{{ __('quiz.fc_hiw_step4_title') }}</div>
        <div class="fc-step-desc">{{ __('quiz.fc_hiw_step4_desc') }}</div>
      </div>
    </div>
  </div>
</div>

{{-- PRICING TEASER --}}
@if($plans->isNotEmpty())
<div class="wrap">
  <div class="fc-pricing">
    <h2 class="fc-pricing-title">{{ __('common.pricing_eyebrow') }}</h2>
    <p class="fc-pricing-sub">{{ __('quiz.fc_pricing_sub') }}</p>
    <div class="fc-plans-grid">
      @foreach($plans as $plan)
        <div class="fc-plan-card {{ $loop->iteration === 2 ? 'fc-plan-card--featured' : '' }}">
          @if($loop->iteration === 2)
            <div class="fc-plan-badge">{{ __('common.pricing_most_popular') }}</div>
          @endif
          <div class="fc-plan-name">{{ $plan->name }}</div>
          <div class="fc-plan-price">
            @if($plan->price_monthly == 0)
              <span class="fc-plan-amt">{{ __('common.pricing_free') }}</span>
            @else
              <span class="fc-plan-amt">{{ $platformSettings->currency_symbol }}{{ number_format($plan->price_monthly, 0) }}</span>
              <span class="fc-plan-per">/mo</span>
            @endif
          </div>
          @if(is_array($plan->features) && count($plan->features))
            <ul class="fc-plan-features">
              @foreach(array_slice($plan->features, 0, 4) as $feature)
                <li>{{ $feature }}</li>
              @endforeach
            </ul>
          @endif
          <a href="{{ route('pricing') }}" class="btn {{ $loop->iteration === 2 ? 'btn-accent' : 'btn-ghost' }} btn-block mt-auto">
            {{ $plan->price_monthly == 0 ? __('common.start_free') : __('common.get_started') }}
          </a>
        </div>
      @endforeach
    </div>
    <p class="fc-pricing-note">
      <a href="{{ route('pricing') }}" class="fc-link-primary">{{ __('quiz.fc_view_full_pricing') }}</a>
    </p>
  </div>
</div>
@endif


@endsection
