@extends('layouts.app')
@section('title', $quiz->title . ' — ' . $platformSettings->app_name)

@if($quiz->meta_description)
@section('meta_description', $quiz->meta_description)
@endif

@if($quiz->meta_keywords)
@section('meta_keywords', $quiz->meta_keywords)
@endif

@if($quiz->cover_image)
@section('og_image', Storage::url($quiz->cover_image))
@endif

@section('og_type', 'article')

@push('styles')
<style>
.qs-mt20{margin-top:20px}
.qs-mt20-block{margin-top:20px;display:block;text-align:center}
.qs-leaderboard-card{margin-bottom:20px;padding:20px 24px}
.qs-leaderboard-title{font-weight:700;font-size:15px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.qs-leaderboard-list{margin:0;padding:0;list-style:none}
.qs-h2-section{font-size:22px;margin:32px 0 0}
.qs-h2-sample{font-size:22px;margin:32px 0 16px}
.qs-sample-pill{margin-bottom:12px}
.qs-sample-q{font-size:18px;font-weight:600;line-height:1.4}
.qs-lock-row{display:flex;align-items:center;gap:8px;font-weight:600}
.qs-creator-sec{font-size:14px}
.qs-desc{font-size:18px;line-height:1.6;margin-bottom:24px}
.qs-fav-label{font-size:13px;color:var(--text-muted);margin-left:8px;vertical-align:middle}
.qs-copy-toast{display:none;text-align:center;font-size:12px;font-weight:600;color:var(--success,#22c55e);margin-top:6px}
</style>
@endpush

@section('content')
<div class="wrap">
  <div class="detail-grid">
    <!-- LEFT -->
    <div>
      <div class="detail-cover cov-1"
        @if($quiz->cover_image)
          style="background-image:url('{{ Storage::url($quiz->cover_image) }}');background-size:cover;background-position:center;"
        @endif
      >
        <span class="qc-cat">{{ $quiz->category->name }}</span>
        <h1>{{ $quiz->title }}</h1>
      </div>

      <div class="card creator-card">
        <span class="avatar avatar-lg" style="background:var(--brand-primary)">{{ strtoupper(substr($quiz->lecturer->name,0,2)) }}</span>
        <div class="creator-meta">
          <div class="nm">{{ $quiz->lecturer->name }}</div>
          <div class="sec qs-creator-sec">{{ __('quiz.lecturer_quizzes_published', ['count' => $quiz->lecturer->quizzes()->published()->count()]) }}</div>
        </div>
      </div>

      @if($quiz->description)
        <p class="sec qs-desc">{{ $quiz->description }}</p>
      @endif

      <h2 class="qs-h2-section">{{ __('quiz.whats_in_this_quiz') }}</h2>
      <div class="whats">
        <div class="whats-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3 5-6"/><circle cx="12" cy="12" r="9"/></svg>
          <div><div class="l">{{ __('quiz.label_questions') }}</div><div class="v">{{ $quiz->total_questions }} · MCQ</div></div>
        </div>
        @if($quiz->duration_minutes)
        <div class="whats-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
          <div><div class="l">{{ __('quiz.label_duration') }}</div><div class="v">{{ __('quiz.label_duration_minutes', ['count' => $quiz->duration_minutes]) }}</div></div>
        </div>
        @endif
        @if($quiz->max_attempts)
        <div class="whats-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v16M4 8h16M9 4v16"/></svg>
          <div><div class="l">{{ __('quiz.label_attempts_allowed') }}</div><div class="v">{{ $quiz->max_attempts }}</div></div>
        </div>
        @endif
        @if($quiz->negative_marking_enabled)
        <div class="whats-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l9 4-9 4-9-4z"/><path d="M21 11l-9 4-9-4"/></svg>
          <div><div class="l">{{ __('quiz.label_negative_marking') }}</div><div class="v">{{ __('quiz.label_negative_enabled') }}</div></div>
        </div>
        @endif
      </div>

      @if($quiz->questions->first())
      <h2 class="qs-h2-sample">{{ __('quiz.sample_question') }}</h2>
      <div class="card sample">
        <div class="pill qs-sample-pill">{{ __('quiz.sample_question_pill', ['total' => $quiz->total_questions, 'marks' => $quiz->questions->first()->marks]) }}</div>
        <div class="serif qs-sample-q">{{ $quiz->questions->first()->content }}</div>
        <div class="sample-opts">
          @foreach($quiz->questions->first()->options->take(1) as $opt)
            <div class="sample-opt"><span class="dot"></span> {{ $opt->content }}</div>
          @endforeach
          @foreach($quiz->questions->first()->options->skip(1) as $opt)
            <div class="sample-opt sample-locked"><span class="dot"></span> {{ $opt->content }}</div>
          @endforeach
        </div>
        @if(!$isEnrolled)
        <div class="sample-lock-cta">
          <div class="sec qs-lock-row">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg>
            {{ __('quiz.unlock_all_questions', ['count' => $quiz->total_questions]) }}
          </div>
          @auth
            @if(($isAssigned ?? false) || ($isEnrolled ?? false))
              <form method="POST" action="{{ route('attempt.start', $quiz->slug) }}">@csrf
                <button type="submit" class="btn btn-primary">{{ __('quiz.enroll_start_exam') }}</button>
              </form>
            @elseif(auth()->user()->role === 'student')
              <p class="muted">{{ __('quiz.not_assigned') }}</p>
            @endif
          @else
            <a href="{{ route('login') }}" class="btn btn-primary">{{ __('quiz.login_to_start') }}</a>
          @endauth
        </div>
        @endif
      </div>
      @endif
    </div>

    <!-- RIGHT STICKY -->
    <aside>
      {{-- Leaderboard: shown only when quiz has at least one completed attempt --}}
      @if(count($leaderboard) > 0)
      <div class="card qs-leaderboard-card">
        <div class="qs-leaderboard-title">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--brand-accent,#E0A431)" stroke-width="2.5"><path d="M12 2l3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/></svg>
          {{ __('quiz.top_scores') }}
        </div>
        <ol class="qs-leaderboard-list">
          @foreach($leaderboard as $rank => $entry)
          <li style="display:flex;align-items:center;gap:12px;padding:12px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-light)' : '' }}">
            <span style="font-family:var(--font-mono);font-size:13px;font-weight:700;width:24px;text-align:center;flex-shrink:0;color:{{ $rank < 3 ? 'var(--brand-accent,#E0A431)' : 'var(--text-muted)' }};">{{ $rank + 1 }}</span>
            <span style="flex:1;font-size:14px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $entry['user_name'] ?? __('quiz.anonymous') }}</span>
            <span style="font-family:var(--font-mono);font-size:13px;font-weight:700;color:var(--success);flex-shrink:0;">{{ number_format($entry['percentage'], 1) }}%</span>
          </li>
          @endforeach
        </ol>
      </div>
      @endif

      @if($quiz->start_at || $quiz->end_at)
      <div id="quizCountdownCard" style="display:none;margin-bottom:16px;border-radius:16px;overflow:hidden;">
        <div id="cdInner" style="padding:20px 22px;">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
            <span id="cdDot" style="width:10px;height:10px;border-radius:50%;display:inline-block;flex-shrink:0;"></span>
            <span id="cdLabel" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;"></span>
          </div>
          <div id="cdTimer" style="font-family:var(--font-mono,monospace);font-size:36px;font-weight:800;line-height:1;letter-spacing:.03em;margin-bottom:6px;"></div>
          <div id="cdSub" style="font-size:12px;opacity:.7;"></div>
        </div>
      </div>
      @endif

      <div class="card buy">
        @if($isEnrolled || ($isAssigned ?? false))
          <form method="POST" action="{{ route('attempt.start', $quiz->slug) }}" class="qs-mt20">@csrf
            <button type="submit" class="btn btn-primary btn-lg btn-block">{{ __('quiz.start_exam') }}</button>
          </form>
        @else
          @auth
            @if(auth()->user()->role === 'student')
              <p class="muted qs-mt20" style="text-align:center;font-size:14px;">{{ __('quiz.not_assigned') }}</p>
            @endif
          @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg btn-block qs-mt20">{{ __('quiz.login_to_start') }}</a>
          @endauth
        @endif

        <div class="buy-pills">
          <div class="buy-pill">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3 5-6"/><circle cx="12" cy="12" r="9"/></svg>
            {{ __('quiz.label_questions') }} <b>{{ $quiz->total_questions }}</b>
          </div>
          @if($quiz->duration_minutes)
          <div class="buy-pill">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
            {{ __('quiz.label_duration') }} <b>{{ __('common.duration_min', ['count' => $quiz->duration_minutes]) }}</b>
          </div>
          @endif
          @if($quiz->max_attempts)
          <div class="buy-pill">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v16M4 8h16M9 4v16"/></svg>
            {{ __('quiz.label_attempts_allowed') }} <b>{{ $quiz->max_attempts }}</b>
          </div>
          @endif
          <div class="buy-pill">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l9 4-9 4-9-4z"/><path d="M21 11l-9 4-9-4"/></svg>
            {{ __('quiz.label_pass_at') }} <b>{{ $quiz->pass_percentage }}%</b>
          </div>
        </div>

        @if($quiz->certificate_enabled)
        <div class="cert-badge-premium">
          <div class="cert-badge-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="12" cy="8" r="5"/>
              <path d="M9 13l-1.5 8L12 19l4.5 2L15 13"/>
              <path d="M10 6l1.5 1.5L14 5"/>
            </svg>
          </div>
          <div class="cert-badge-text">
            <div class="cert-badge-title">{{ __('quiz.certificate_verified') }}</div>
            <div class="cert-badge-sub">{{ __('quiz.certificate_pass_earn') }}</div>
          </div>
        </div>
        @endif

        @auth
        <div class="mb-4">
            <livewire:toggle-favourite :quizId="$quiz->id" :key="'fav-show-'.$quiz->id" />
            <span class="qs-fav-label">{{ __('quiz.save_to_favourites') }}</span>
        </div>
        @endauth

        <div class="share-label">{{ __('quiz.share_this_quiz') }}</div>
        <div class="share-row">
          {{-- Copy link --}}
          <button type="button" class="share-btn" id="copyLinkBtn" title="Copy link">
            <svg id="copyIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1M14 11a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1"/></svg>
            <svg id="checkIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" hidden><path d="M5 13l4 4L19 7"/></svg>
          </button>
          {{-- WhatsApp --}}
          <a class="share-btn share-btn--wa" target="_blank" rel="noopener"
             href="https://wa.me/?text={{ urlencode('Check out this quiz: ' . $quiz->title . ' ' . request()->url()) }}"
             title="Share on WhatsApp">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.47 14.38c-.27-.13-1.57-.77-1.81-.86s-.42-.13-.6.13-.69.86-.84 1.03-.31.19-.58.06a7.34 7.34 0 01-2.16-1.33 8.07 8.07 0 01-1.5-1.86c-.16-.27 0-.42.12-.55s.27-.31.4-.47a1.84 1.84 0 00.26-.44.48.48 0 000-.46c-.06-.13-.6-1.44-.82-1.97s-.43-.45-.59-.46h-.51a1 1 0 00-.72.34 3 3 0 00-.93 2.23 5.24 5.24 0 001.1 2.78 12 12 0 004.58 4.06c.64.27 1.14.44 1.53.56a3.69 3.69 0 001.69.11 2.76 2.76 0 001.81-1.28 2.24 2.24 0 00.16-1.28c-.06-.11-.24-.17-.51-.3zM12 2a10 10 0 00-8.67 14.95L2 22l5.23-1.37A10 10 0 1012 2z"/></svg>
          </a>
          {{-- Telegram --}}
          <a class="share-btn share-btn--tg" target="_blank" rel="noopener"
             href="https://t.me/share/url?url={{ urlencode(request()->url()) }}&text={{ urlencode('Check out this quiz: ' . $quiz->title) }}"
             title="Share on Telegram">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8l-1.7 8c-.12.54-.46.67-.93.42l-2.57-1.9-1.24 1.19c-.14.14-.25.25-.51.25l.18-2.6 4.7-4.25c.2-.18-.04-.28-.32-.1l-5.82 3.67-2.5-.78c-.54-.17-.55-.54.12-.8l9.77-3.77c.45-.16.85.11.82.67z"/></svg>
          </a>
          {{-- LinkedIn --}}
          <a class="share-btn share-btn--li" target="_blank" rel="noopener"
             href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}"
             title="Share on LinkedIn">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.36V9h3.41v1.56h.05c.47-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 110-4.12 2.06 2.06 0 010 4.12zM7.12 20.45H3.56V9h3.56v11.45zM22.23 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.46C23.21 24 24 23.23 24 22.27V1.73C24 .77 23.21 0 22.23 0z"/></svg>
          </a>
          {{-- Facebook --}}
          <a class="share-btn share-btn--fb" target="_blank" rel="noopener"
             href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
             title="Share on Facebook">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.07C24 5.41 18.63 0 12 0S0 5.41 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.95.93-1.95 1.88v2.27h3.32l-.53 3.49h-2.79V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
          </a>
          {{-- Twitter / X --}}
          <a class="share-btn share-btn--tw" target="_blank" rel="noopener"
             href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode('Just took: ' . $quiz->title . ' — try it!') }}"
             title="Share on X (Twitter)">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 5.8a8 8 0 01-2.3.6 4 4 0 001.8-2.2 8 8 0 01-2.5 1A4 4 0 0012 8.8a11 11 0 01-8-4 4 4 0 001.2 5.3A4 4 0 013 9.5a4 4 0 003.2 4 4 4 0 01-1.8.1 4 4 0 003.7 2.8A8 8 0 012 18.6a11 11 0 006 1.8c7.2 0 11.2-6 11.2-11.2v-.5A8 8 0 0022 5.8z"/></svg>
          </a>
        </div>
        <div id="copyToast" class="qs-copy-toast">{{ __('quiz.link_copied') }}</div>
      </div>
    </aside>
  </div>
</div>
@if($quiz->start_at || $quiz->end_at)
@push('scripts')
<script>
(function () {
'use strict';
  // UTC unix timestamps in milliseconds — no TZ conversion needed
  const startMs = {{ $quiz->start_at ? $quiz->start_at->utc()->timestamp . '000' : 'null' }};
  const endMs   = {{ $quiz->end_at   ? $quiz->end_at->utc()->timestamp   . '000' : 'null' }};

  const wrap  = document.getElementById('quizCountdownCard');
  const inner = document.getElementById('cdInner');
  const dot   = document.getElementById('cdDot');
  const label = document.getElementById('cdLabel');
  const timer = document.getElementById('cdTimer');
  const sub   = document.getElementById('cdSub');

  function pad(n) { return String(n).padStart(2, '0'); }

  function fmt(diffMs) {
    const total = Math.max(0, Math.floor(diffMs / 1000));
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = total % 60;
    return h > 0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(m)}:${pad(s)}`;
  }

  function localDateStr(ms) {
    return new Date(ms).toLocaleString(undefined, {
      day: '2-digit', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });
  }

  function applyTheme(bg, border, color, dotColor) {
    inner.style.background = bg;
    wrap.style.border = `1.5px solid ${border}`;
    wrap.style.boxShadow = `0 4px 20px ${border}40`;
    dot.style.background = dotColor;
    dot.style.boxShadow = `0 0 0 3px ${dotColor}33`;
    label.style.color = color;
    timer.style.color = color;
  }

  function tick() {
    const now = Date.now();

    if (startMs && now < startMs) {
      wrap.style.display = '';
      applyTheme('linear-gradient(135deg,#fffbeb,#fef3c7)', '#f59e0b', '#92400e', '#f59e0b');
      label.textContent = 'Starts in';
      timer.textContent = fmt(startMs - now);
      sub.textContent   = `Opens ${localDateStr(startMs)}`;
      sub.style.color   = '#92400e';
      return;
    }

    if (endMs && now < endMs) {
      wrap.style.display = '';
      applyTheme('linear-gradient(135deg,#ecfdf5,#d1fae5)', '#10b981', '#065f46', '#10b981');
      label.textContent = 'Ends in';
      timer.textContent = fmt(endMs - now);
      sub.textContent   = `Closes ${localDateStr(endMs)}`;
      sub.style.color   = '#065f46';
      return;
    }

    if (endMs && now >= endMs) {
      wrap.style.display = '';
      applyTheme('#f9fafb', '#d1d5db', '#6b7280', '#9ca3af');
      label.textContent = 'Quiz ended';
      timer.textContent = '—';
      timer.style.fontSize = '28px';
      sub.textContent   = `Ended ${localDateStr(endMs)}`;
      sub.style.color   = '#9ca3af';
      clearInterval(interval);
      return;
    }

    wrap.style.display = 'none';
    clearInterval(interval);
  }

  tick();
  const interval = setInterval(tick, 1000);
})();
</script>
@endpush
@endif

@push('scripts')
<script>
(function () {
'use strict';
  const btn   = document.getElementById('copyLinkBtn');
  const copy  = document.getElementById('copyIcon');
  const check = document.getElementById('checkIcon');
  const toast = document.getElementById('copyToast');
  if (!btn) return;
  btn.addEventListener('click', function () {
    navigator.clipboard.writeText(window.location.href).then(function () {
      copy.style.display  = 'none';
      check.style.display = '';
      btn.style.color     = 'var(--success,#22c55e)';
      toast.style.display = '';
      setTimeout(function () {
        copy.style.display  = '';
        check.style.display = 'none';
        btn.style.color     = '';
        toast.style.display = 'none';
      }, 2000);
    });
  });
})();
</script>
@endpush

@endsection
