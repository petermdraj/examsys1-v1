{{-- Professionals homepage theme --}}
<style>
.pro-eyebrow-mb{margin-bottom:16px}
.pro-hero-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:28px}
.pro-section-head-mt{margin-top:48px}
.pro-scroll-mb{margin-bottom:56px}
.pro-grid-mb{margin-bottom:64px}
.pro-link-bold{font-weight:600;font-size:14px}
.pro-avatar{background:var(--brand-primary)}
.pro-avg{font-size:12px}
.pro-wrap-pt{padding-top:48px}
.pro-empty{text-align:center;padding:64px 24px;color:var(--text-muted)}
.pro-link-primary{color:var(--brand-primary);font-weight:600}
.pro-card-link{text-decoration:none}
</style>

{{-- HERO --}}
<div class="hero">
  <div class="hero-grid">
    <div>
      <div class="eyebrow pro-eyebrow-mb">{{ __('common.professionals_hero_eyebrow') }}</div>
      <h1>{{ __('common.professionals_hero_h1') }}</h1>
      <p class="hero-sub">{{ __('common.professionals_hero_sub') }}</p>
      <div class="pro-hero-actions">
        <a href="{{ route('quizzes.index') }}" class="btn btn-primary btn-lg">
          {{ __('common.professionals_find_cert') }}
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
        <a href="{{ route('categories.index') }}" class="btn btn-ghost btn-lg">{{ __('common.professionals_browse_domains') }}</a>
      </div>
    </div>
    <div class="hero-cert-mockup hero-cert-mockup--pro" aria-hidden="true">
      <div class="cert-card cert-card--pro">
        <div class="cert-badge cert-badge--pro">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="9" r="5"/><path d="M9 13l-1 7 4-2 4 2-1-7"/></svg>
        </div>
        <div class="cert-title">{{ __('common.professionals_cert_title') }}</div>
        <div class="cert-name">Priya Mehta</div>
        <div class="cert-quiz">Advanced Financial Analysis</div>
        <div class="cert-score">Score: 91% · Distinction</div>
        <div class="cert-qr">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28" height="28"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="8" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/><path d="M13 13h2v2h-2zM17 13h4v2h-4zM19 17v4h-2v-2h-4v-2h4z"/></svg>
          <span class="cert-qr-label">QR Verified</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="wrap pro-wrap-pt">

  {{-- HOW IT WORKS --}}
  <div class="how-it-works">
    <div class="hiw-step">
      <div class="hiw-num">1</div>
      <div class="hiw-content">
        <div class="hiw-title">{{ __('common.hiw_step1_domain_title') }}</div>
        <div class="hiw-desc">{{ __('common.hiw_step1_domain_desc') }}</div>
      </div>
    </div>
    <div class="hiw-connector" aria-hidden="true"></div>
    <div class="hiw-step">
      <div class="hiw-num">2</div>
      <div class="hiw-content">
        <div class="hiw-title">{{ __('common.hiw_step2_verify_title') }}</div>
        <div class="hiw-desc">{{ __('common.hiw_step2_verify_desc') }}</div>
      </div>
    </div>
    <div class="hiw-connector" aria-hidden="true"></div>
    <div class="hiw-step">
      <div class="hiw-num">3</div>
      <div class="hiw-content">
        <div class="hiw-title">{{ __('common.hiw_step3_share_title') }}</div>
        <div class="hiw-desc">{{ __('common.hiw_step3_share_desc') }}</div>
      </div>
    </div>
  </div>

  {{-- STATS STRIP --}}
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
    <div class="stats-strip">
      @if($s1v)<div class="stat-item"><div class="stat-value">{{ $s1v }}</div>@if($s1l)<div class="stat-label">{{ $s1l }}</div>@endif</div>@endif
      @if($s2v)<div class="stat-item"><div class="stat-value">{{ $s2v }}</div>@if($s2l)<div class="stat-label">{{ $s2l }}</div>@endif</div>@endif
      @if($s3v)<div class="stat-item"><div class="stat-value">{{ $s3v }}</div>@if($s3l)<div class="stat-label">{{ $s3l }}</div>@endif</div>@endif
    </div>
  @endif

  {{-- CATEGORIES --}}
  @if($categories->isNotEmpty())
    <div class="section-head pro-section-head-mt">
      <h2>{{ __('common.professional_domains') }}</h2>
      <a class="sec pro-link-bold" href="{{ route('quizzes.index') }}">{{ __('common.view_all') }} →</a>
    </div>
    <div class="scroll-x pro-scroll-mb">
      <div class="cat-strip">
        @foreach($categories as $cat)
          <a href="{{ route('quizzes.index', ['category' => $cat->slug]) }}" class="cat-pill">
            <span class="cat-ic" style="background:{{ $cat->color ?? '#F4EAF1' }}1A;">
              <svg viewBox="0 0 24 24" fill="none" stroke="{{ $cat->color ?? 'var(--brand-primary)' }}" stroke-width="2"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z"/></svg>
            </span>
            {{ $cat->name }}
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- FEATURED --}}
  <div class="section-head">
    <h2>{{ __('common.featured_certifications') }}</h2>
    <a class="sec pro-link-bold" href="{{ route('quizzes.index') }}">{{ __('common.see_all') }}</a>
  </div>
  @if($featured->isEmpty())
    <div class="pro-empty">
      <p>{{ __('common.no_certs_published') }} <a href="{{ route('for-creators') }}" class="pro-link-primary">{{ __('common.publish_first_cert') }}</a></p>
    </div>
  @else
    <div class="grid-3 pro-grid-mb">
      @foreach($featured as $i => $quiz)
        <a href="{{ route('quizzes.show', $quiz->slug) }}" class="card quiz-card pro-card-link">
          <div class="qc-cover cov-{{ ($i % 9) + 1 }}" @if($quiz->cover_image) style="background-image:url('{{ Storage::url($quiz->cover_image) }}');background-size:cover;background-position:center;" @endif>
            <span class="qc-cat">{{ $quiz->category->name }}</span>
            @if($quiz->isFree())<span class="badge badge-free">{{ __('common.free_badge') }}</span>
            @else<span class="badge badge-price">{{ $platformSettings->currency_symbol }}{{ number_format($quiz->price, 0) }}</span>@endif
          </div>
          <div class="qc-body">
            <div class="qc-title">{{ $quiz->title }}</div>
            <div class="qc-creator">
              <span class="avatar pro-avatar">{{ strtoupper(substr($quiz->creator->name,0,2)) }}</span>
              {{ $quiz->creator->name }}
            </div>
            <div class="qc-meta">
              <span class="pill">{{ $quiz->total_questions }} {{ __('common.quiz_questions') }}</span>
              @if($quiz->duration_minutes)<span class="pill">{{ $quiz->duration_minutes }} {{ __('common.quiz_min') }}</span>@endif
            </div>
            <div class="qc-foot">
              <span class="qc-attempts">{{ number_format($quiz->total_attempts) }} {{ __('common.quiz_attempts') }}</span>
              @if($quiz->average_score > 0)<span class="muted pro-avg">{{ __('common.quiz_avg') }} {{ $quiz->average_score }}%</span>@endif
            </div>
          </div>
        </a>
      @endforeach
    </div>
  @endif

  <div class="creator-teaser">
    <span>{{ __('common.creator_teaser_professionals') }}</span>
    <a href="{{ route('for-creators') }}">{{ __('common.creator_teaser_link_professionals') }}</a>
  </div>
</div>
