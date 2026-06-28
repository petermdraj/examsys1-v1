{{-- Competition / Default homepage theme --}}
<style>
.cmp-wrap-inner{padding-top:0;padding-bottom:0}
.cmp-hero-pb{padding-bottom:40px}
.cmp-legend-dot{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:3px}
.cmp-section-head-mb{margin-bottom:20px}
.cmp-h2-sm{font-size:22px}
.cmp-view-all{font-weight:600;font-size:14px;color:var(--brand-primary)}
.cmp-cta-btn{flex:none}
.cmp-wrap-pt8{padding-top:8px}
.cmp-empty{text-align:center;padding:64px 24px;color:var(--text-muted)}
.cmp-grid-mb{margin-bottom:56px}
.cmp-legend-green{background:#22C55E}
.cmp-legend-red{background:#EF4444;margin-left:6px}
.cmp-legend-purple{background:#8B5CF6;margin-left:6px}
.cmp-card-link{text-decoration:none}
</style>

{{-- HERO --}}
@php
    $heroCtaUrl = $platformSettings->hero_cta_url ?: route('quizzes.index');
@endphp
<div class="hero-v2">
  <div class="hero-v2-arch" aria-hidden="true"></div>
  <div class="wrap cmp-wrap-inner">
    <div class="hero-v2-grid cmp-hero-pb">
      <div class="hero-v2-text">
        <h1 class="hero-v2-h1">{!! nl2br(e($platformSettings->hero_title)) !!}</h1>
        <p class="hero-v2-sub">{{ $platformSettings->hero_subtitle }}</p>
        <div class="hero-v2-actions">
          <a href="{{ $heroCtaUrl }}" class="btn btn-accent btn-lg">{{ $platformSettings->hero_cta_text }}</a>
          <a href="#how-it-works" class="btn-play-link">
            <span class="play-circle">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            </span>
            {{ $platformSettings->hero_secondary_text }}
          </a>
        </div>
      </div>

      {{-- IBPS exam panel card (light) --}}
      <div class="ibps-card" aria-hidden="true">
        <div class="ibps-card-header">
          <span class="ibps-card-timer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            42:18
          </span>
          <span class="ibps-card-badge">IBPS PO Prelims</span>
          <span class="ibps-card-expand">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M8 3H5a2 2 0 0 0-2 2v3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
          </span>
        </div>
        <div class="ibps-card-meta">
          <span>Question 12 / 100</span>
          <span class="ibps-legend">
            <span class="ibps-legend-dot cmp-legend-green"></span> {{ __('common.cmp_answered') }}
            <span class="ibps-legend-dot cmp-legend-red"></span> {{ __('common.cmp_unanswered') }}
            <span class="ibps-legend-dot cmp-legend-purple"></span> {{ __('common.cmp_marked') }}
          </span>
        </div>
        <div class="ibps-card-body">
          <div class="ibps-card-left">
            <div class="ibps-card-q">
              If <em>x</em> + 7<em>y</em> = 25 and 2<em>x</em> – 3<em>y</em> = 4, then what is the value of 3<em>x</em> + 2<em>y</em>?
            </div>
            <div class="ibps-card-opts">
              <label class="ibps-card-opt"><span class="ibps-radio"></span> A. 29</label>
              <label class="ibps-card-opt"><span class="ibps-radio"></span> B. 31</label>
              <label class="ibps-card-opt"><span class="ibps-radio"></span> C. 32</label>
              <label class="ibps-card-opt"><span class="ibps-radio"></span> D. 34</label>
            </div>
          </div>
          <div class="ibps-card-right">
            <div class="ibps-card-palette">
              @php
                $answered = [1,2,3,6,7,11,12,16,21];
                $unanswered = [8,13,14,15,18,19,20,22,23];
                $marked = [4,9];
              @endphp
              @for($i = 1; $i <= 25; $i++)
                <span class="pal-lgt
                  @if(in_array($i, $answered)) pal-lgt--answered
                  @elseif(in_array($i, $unanswered)) pal-lgt--unanswered
                  @elseif(in_array($i, $marked)) pal-lgt--marked
                  @else pal-lgt--unvisited
                  @endif
                ">{{ $i }}</span>
              @endfor
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- EXPLORE BY CATEGORY STRIP --}}
@if($categories->isNotEmpty())
@php
$catIcons = [
  'technology'         => '<path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2v-4M9 21H5a2 2 0 0 1-2-2v-4m0 0h18"/>',
  'mathematics'        => '<path d="M4 6h16M4 12h8m-8 6h16"/><path d="M16 9l4 3-4 3"/>',
  'history'            => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
  'geography'          => '<circle cx="12" cy="12" r="9"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
  'language-literature'=> '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
  'general-knowledge'  => '<circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/>',
  'business-finance'   => '<path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 10v11M12 10v11M16 10v11"/>',
  'aptitude-reasoning' => '<path d="M9 3L5 9l7 13 7-13-4-6H9z"/><path d="M5 9h14"/>',
  'programming'        => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
  'medical-health'     => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
  'law-governance'     => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
  'environment'        => '<path d="M17 8C8 10 5.9 16.17 3.82 19.38A1 1 0 0 0 4.7 21C15 14 18 7.5 18 6c0-.5-.2-.9-.5-1.2"/><path d="M3 21c0-7 4-12 9-14"/>',
  'arts-culture'       => '<circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>',
  'sports'             => '<circle cx="12" cy="12" r="9"/><path d="M4.93 4.93l4.24 4.24M14.83 14.83l4.24 4.24M7.76 7.76L9.9 9.9M14.1 14.1l2.14 2.14M4.93 19.07l4.24-4.24M14.83 9.17l4.24-4.24"/>',
  'current-affairs'    => '<path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/><path d="M8 7h8M8 11h8M8 15h4"/>',
  'economics'          => '<path d="M18 20V10M12 20V4M6 20v-6"/>',
  'psychology'         => '<path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2z"/>',
  'engineering'        => '<circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>',
  'competitive-exams'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h4"/>',
];
@endphp
<div class="cat-explore-strip">
  <div class="wrap cmp-wrap-inner">
    <div class="cat-explore-inner">
      <span class="cat-explore-label">{{ __('common.explore_by_category') }}</span>
      <div class="cat-explore-scroll">
        @foreach($categories as $cat)
          @php $iconPath = $catIcons[$cat->slug] ?? '<path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z"/>'; @endphp
          <a href="{{ route('quizzes.index', ['category' => $cat->slug]) }}" class="cat-explore-pill">
            <span class="cat-explore-icon" style="background:{{ $cat->color ?? 'var(--brand-primary)' }}18;">
              <svg viewBox="0 0 24 24" fill="none" stroke="{{ $cat->color ?? 'var(--brand-primary)' }}" stroke-width="1.8">{!! $iconPath !!}</svg>
            </span>
            {{ $cat->name }}
          </a>
        @endforeach
      </div>
      <a href="{{ route('categories.index') }}" class="cat-explore-arrow" title="View all categories">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path d="M9 18l6-6-6-6"/></svg>
      </a>
    </div>
  </div>
</div>
@endif

{{-- HOW IT WORKS --}}
@php
    $hiwSteps = is_array($platformSettings->hiw_steps) ? $platformSettings->hiw_steps : (json_decode($platformSettings->hiw_steps ?? '[]', true) ?: []);
    $hiwIcons = [
        0 => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/>',
        1 => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/><path d="M7 8h.01M11 8h6M7 12h6"/>',
        2 => '<path d="M8 21h8M12 15v6"/><path d="M12 2l1.5 4L17 7l-3 2.5L15 13l-3-1.5L9 13l1-3.5L7 7l3.5-.5z"/>',
        3 => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        4 => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
        5 => '<path d="M5 12h14M12 5l7 7-7 7"/>',
    ];
@endphp
<div id="how-it-works" class="hiw-v2-wrap">
  <div class="wrap cmp-wrap-inner">
    <div class="hiw-v2-section-head">
      <h2 class="hiw-v2-section-title">{{ $platformSettings->hiw_title }}</h2>
      <p class="hiw-v2-section-sub">{{ $platformSettings->hiw_subtitle }}</p>
    </div>
    <div class="hiw-v2-cards">
      @foreach($hiwSteps as $idx => $step)
      <div class="hiw-v2-card">
        <div class="hiw-v2-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $hiwIcons[$idx] ?? $hiwIcons[0] !!}</svg>
        </div>
        <div class="hiw-v2-card-body">
          <div class="hiw-v2-card-num">{{ $step['num_label'] ?? '' }}</div>
          <div class="hiw-v2-card-title">{{ $step['title'] ?? '' }}</div>
          <div class="hiw-v2-card-desc">{{ $step['desc'] ?? '' }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>

{{-- FEATURED QUIZZES --}}
<div class="wrap cmp-wrap-pt8">
  <div class="section-head cmp-section-head-mb">
    <h2 class="cmp-h2-sm">{{ $platformSettings->featured_title ?: __('common.cmp_featured_quizzes_fallback') }}</h2>
    <a href="{{ route('quizzes.index') }}" class="cmp-view-all">{{ __('common.cmp_view_all_quizzes') }}</a>
  </div>

  @if($featured->isEmpty())
    <div class="cmp-empty">
      <p>{{ __('common.no_quizzes_competition') }} <a href="{{ route('for-creators') }}" class="cmp-view-all">{{ __('common.create_first_one') }}</a></p>
    </div>
  @else
    <div class="grid-3 cmp-grid-mb">
      @foreach($featured as $i => $quiz)
        <a href="{{ route('quizzes.show', $quiz->slug) }}" class="quiz-card-v2 cmp-card-link">
          <div class="qc2-cover cov-{{ ($i % 9) + 1 }}"
            @if($quiz->cover_image)
              style="background-image:url('{{ Storage::url($quiz->cover_image) }}');background-size:cover;background-position:center;"
            @endif
          >
            <span class="qc2-cat-badge">{{ $quiz->category->name }}</span>
            <div class="qc2-cover-title">{{ $quiz->title }}</div>
          </div>
          <div class="qc2-foot">
            <div class="qc2-creator">
              <span class="qc2-avatar">{{ strtoupper(substr($quiz->creator->name,0,2)) }}</span>
              <span class="qc2-creator-name">{{ $quiz->creator->name }}</span>
              <svg class="qc2-verified" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="qc2-price">
              @if($quiz->isFree())
                <span class="qc2-badge-free">{{ __('common.free_badge') }}</span>
              @else
                <span class="qc2-badge-price">{{ $platformSettings->currency_symbol }}{{ number_format($quiz->price, 0) }}</span>
              @endif
            </div>
          </div>
          <div class="qc2-stats">
            <span class="qc2-stat">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
              {{ $quiz->total_questions }} {{ __('common.quiz_questions') }}
            </span>
            @if($quiz->duration_minutes)
            <span class="qc2-stat">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
              {{ $quiz->duration_minutes }} {{ __('common.quiz_min') }}
            </span>
            @endif
            <span class="qc2-stat">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              {{ number_format($quiz->total_attempts) }} {{ __('common.quiz_attempts') }}
            </span>
          </div>
        </a>
      @endforeach
    </div>
  @endif

  {{-- CREATOR CTA STRIP --}}
  @php
      $ctaUrl = $platformSettings->creator_cta_url ?: route('for-creators');
  @endphp
  <div class="creator-cta-dark">
    <div class="creator-cta-content">
      <div class="creator-cta-icon-wrap">
        <svg viewBox="0 0 24 24" fill="none" width="28" height="28"><circle cx="12" cy="8" r="5" fill="rgba(224,164,49,.25)" stroke="var(--brand-accent)" stroke-width="1.5"/><path d="M3 21c0-4.418 4.03-8 9-8s9 3.582 9 8" stroke="var(--brand-accent)" stroke-width="1.5" stroke-linecap="round"/><path d="M16 4l.8 2.1L19 7l-2.2.9L16 10l-.8-2.1L13 7l2.2-.9z" fill="var(--brand-accent)"/></svg>
      </div>
      <div class="creator-cta-text">
        <div class="creator-cta-title">{{ $platformSettings->creator_cta_title }}</div>
        <div class="creator-cta-sub">{{ $platformSettings->creator_cta_sub }}</div>
      </div>
      <a href="{{ $ctaUrl }}" class="btn btn-accent cmp-cta-btn">{{ $platformSettings->creator_cta_btn }}</a>
    </div>
    <div class="creator-cta-decor" aria-hidden="true">
      <svg viewBox="0 0 80 80" fill="none" width="80" height="80" opacity=".12"><path d="M40 5L48 28H73L53 43L61 66L40 52L19 66L27 43L7 28H32Z" fill="white"/></svg>
    </div>
  </div>
</div>
