@extends('layouts.app')
@section('title', __('exam.your_result') . ' — ' . $attempt->quiz->title)

@push('styles')
<style>
/* ── Layout ─────────────────────────────────────────────── */
.res-mb32{margin-bottom:32px}
.res-eyebrow-mb{margin-bottom:8px}
.res-h1{font-size:clamp(26px,5vw,40px);font-weight:800;line-height:1.1}
.res-section-head-mb{margin-bottom:16px}
.res-h2{font-size:22px}
.res-tap-hint{font-size:14px}
.res-time-label{margin:0}
.res-stat-success{color:var(--success)}
.res-stat-danger{color:var(--danger)}
.res-stat-muted{color:var(--text-muted)}
.res-cert-btn{margin-bottom:12px;display:flex;align-items:center;justify-content:center;gap:8px}
.res-retry-btn{margin-bottom:12px}
.res-browse-btn{text-align:center}
.res-ans-label{margin-right:8px}
.res-opt-letter{width:18px}
.res-time-note{font-weight:400;color:var(--text-muted)}
.res-opt-mb{margin-bottom:8px}

/* ── Score card hero band ───────────────────────────────── */
.score-hero{
  border-radius:16px 16px 0 0;
  margin:-32px -24px 24px;
  padding:28px 24px 32px;
  display:flex;flex-direction:column;align-items:center;gap:0;
  position:relative;overflow:hidden;
}
.score-hero.pass-hero{background:linear-gradient(145deg,#14532d,#16a34a);}
.score-hero.fail-hero{background:linear-gradient(145deg,#450a0a,#dc2626);}
.score-hero::after{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse at 80% 20%,rgba(255,255,255,.12) 0%,transparent 65%);
  pointer-events:none;
}
/* Ring inside hero — white track */
.score-hero .ring-wrap{margin-bottom:16px;}
.score-hero .ring-center .ring-pct,
.score-hero .ring-center .ring-label{color:#fff;}
.score-hero .ring-label{opacity:.8;}
/* Pass/fail badge inside hero */
.score-hero .pass-badge{margin-bottom:0;background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.3);}

/* ── Score summary bar ──────────────────────────────────── */
.res-summary-bar{display:flex;gap:0;border-radius:12px;overflow:hidden;margin-bottom:16px;height:8px;}
.res-summary-bar span{display:block;transition:width .8s ease;}
.res-summary-bar .sb-correct{background:var(--success);}
.res-summary-bar .sb-wrong{background:var(--danger);}
.res-summary-bar .sb-skip{background:var(--border);}

/* ── Stat cells — 3-up horizontal on mobile ─────────────── */
.stat-3{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px;}
.stat-3 .stat-cell{padding:12px 10px;text-align:center;}
.stat-3 .stat-cell .v{font-size:24px;}
.stat-3 .stat-cell .l{font-size:11px;}
.stat-time{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;
  background:var(--surface-2);border-radius:10px;margin-bottom:24px;}

@media(max-width:480px){
  .score-hero{margin:-24px -16px 20px;padding:22px 16px 26px;}
  .score-hero .ring-wrap{width:140px;height:140px;}
  .score-hero .ring-wrap svg{width:140px;height:140px;}
  .score-hero .ring-pct{font-size:32px;}
}
</style>
@endpush

@section('content')
<div class="wrap">
  <div class="res-mb32">
    <div class="eyebrow res-eyebrow-mb">{{ __('exam.attempt_complete') }}</div>
    <h1 class="res-h1">{{ __('exam.your_result') }}</h1>
  </div>

  <div class="result-grid">
    <!-- SCORE CARD -->
    <aside>
      <div class="card score-card">

        {{-- Coloured hero band with ring -----------------------------------------------}}
        <div class="score-hero {{ $attempt->is_passed ? 'pass-hero' : 'fail-hero' }}">
          <div class="ring-wrap">
            <svg width="200" height="200" viewBox="0 0 200 200">
              <circle cx="100" cy="100" r="84" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="16"/>
              <circle id="scoreRingFill" cx="100" cy="100" r="84" fill="none"
                stroke="rgba(255,255,255,.9)"
                stroke-width="16" stroke-linecap="round"
                style="transition:stroke-dashoffset 1.4s cubic-bezier(.4,0,.1,1);"/>
            </svg>
            <div class="ring-center">
              <div class="ring-pct"><span id="scoreNum">0</span><small>%</small></div>
              <div class="ring-label">{{ __('exam.score_label') }}</div>
            </div>
          </div>

          @if($attempt->is_passed)
            <div class="pass-badge pass">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12l5 5L20 7"/></svg>
              {{ __('exam.passed_cutoff', ['pct' => $attempt->quiz->pass_percentage]) }}
            </div>
          @else
            <div class="pass-badge fail">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
              {{ __('exam.not_passed_need', ['pct' => $attempt->quiz->pass_percentage]) }}
            </div>
          @endif
        </div>

        {{-- Stats -----------------------------------------------------------------------}}
        @php
          $correct    = $attempt->answers->where('is_correct', true)->count();
          $unanswered = $attempt->answers->filter(fn($a) => empty($a->selected_options) && !$a->text_answer)->count();
          $wrong      = $attempt->answers->count() - $correct - $unanswered;
          $total      = max(1, $attempt->answers->count());
          $pctCorrect  = round($correct / $total * 100);
          $pctWrong    = round($wrong / $total * 100);
          $pctSkip     = 100 - $pctCorrect - $pctWrong;
        @endphp

        {{-- Visual proportion bar --}}
        <div class="res-summary-bar">
          <span class="sb-correct" style="width:{{ $pctCorrect }}%"></span>
          <span class="sb-wrong"   style="width:{{ $pctWrong }}%"></span>
          <span class="sb-skip"    style="width:{{ $pctSkip }}%"></span>
        </div>

        {{-- 3-up stat cells --}}
        <div class="stat-3">
          <div class="stat-cell"><div class="v res-stat-success">{{ $correct }}</div><div class="l">{{ __('exam.correct') }}</div></div>
          <div class="stat-cell"><div class="v res-stat-danger">{{ $wrong }}</div><div class="l">{{ __('exam.wrong') }}</div></div>
          <div class="stat-cell"><div class="v res-stat-muted">{{ $unanswered }}</div><div class="l">{{ __('exam.unanswered') }}</div></div>
        </div>

        {{-- Score + Time row --}}
        <div class="stat-3" style="margin-bottom:16px;">
          <div class="stat-cell" style="grid-column:1/-1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div>
              <div class="l" style="margin-bottom:4px;">{{ __('exam.total_marks') }}</div>
              <div class="v">{{ $attempt->score }} / {{ $attempt->total_marks }}</div>
            </div>
            <div style="text-align:right;">
              <div class="l" style="margin-bottom:4px;">{{ __('exam.time_taken') }}
                @if($attempt->quiz->duration_minutes)
                  <span class="res-time-note"> / {{ $attempt->quiz->duration_minutes }}m</span>
                @endif
              </div>
              @php
                $secs = $attempt->time_taken_seconds ?? 0;
                if ($secs <= 0 && $attempt->submitted_at && $attempt->started_at) {
                    $secs = $attempt->started_at->diffInSeconds($attempt->submitted_at);
                }
                $secs = max(0, $secs);
                $h = intdiv($secs, 3600);
                $m = intdiv($secs % 3600, 60);
                $s = $secs % 60;
                $timeFmt = $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
              @endphp
              <div class="v mono">{{ $timeFmt }}</div>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        @if($attempt->is_passed && $attempt->quiz->certificate_enabled)
          <a href="{{ route('certificate.download', $attempt->id) }}"
             class="btn btn-accent btn-block res-cert-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M8 11l4 4 4-4M5 21h14"/></svg>
            {{ __('exam.download_certificate') }}
          </a>
        @endif
        @if(!$attempt->is_passed)
          <button class="btn btn-accent btn-block res-retry-btn" onclick="document.getElementById('retryModal').classList.add('open')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 005.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 013.51 15"/></svg>
            {{ __('exam.retry_quiz') }}
          </button>
        @endif
        <a href="{{ route('quizzes.index') }}" class="btn btn-ghost btn-block res-browse-btn">{{ __('exam.browse_more_quizzes') }}</a>
      </div>
    </aside>

    <!-- REVIEW -->
    <div>
      <div class="section-head res-section-head-mb">
        <h2 class="res-h2">{{ __('exam.question_review_heading') }}</h2>
        <span class="muted res-tap-hint">{{ __('exam.tap_to_expand') }}</span>
      </div>
      <div class="card">
        <div class="rev-list">
          @foreach($attempt->answers as $i => $answer)
            @php
              $letters = ['A','B','C','D','E','F'];
              $hasSelected = !empty($answer->selected_options) || $answer->text_answer;
              $statusClass = $answer->is_correct ? 'correct' : ($hasSelected ? 'wrong' : 'skip');
              $statusLabel = $answer->is_correct ? __('exam.status_correct') : ($hasSelected ? __('exam.status_wrong') : __('exam.status_skipped'));
              $marks = $answer->is_correct ? '+' . $answer->marks_earned : ($hasSelected ? '−' . $answer->question->negative_marks : '0');
            @endphp
            <div class="rev-row" id="row-{{ $i }}">
              <button class="rev-head" onclick="document.getElementById('row-{{ $i }}').classList.toggle('open')">
                <span class="rev-qn mono">Q{{ $i + 1 }}</span>
                <span class="rev-snip">{{ Str::limit(strip_tags($answer->question->content), 60) }}</span>
                <span class="rev-status {{ $statusClass }}">{{ $statusLabel }}</span>
                <span class="rev-marks" style="color:{{ $answer->is_correct ? 'var(--success)' : ($hasSelected ? 'var(--danger)' : 'var(--text-muted)') }}">{{ $marks }}</span>
                <svg class="rev-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
              </button>
              <div class="rev-body">
                <div class="rev-q">{!! $answer->question->content !!}</div>
                @if($answer->question->type === 'fill_blank')
                  <div class="rev-opt res-opt-mb {{ $answer->text_answer ? ($answer->is_correct ? 'correct' : 'wrong') : '' }}">
                    <b class="res-ans-label">{{ __('exam.your_answer') }}</b>
                    <span>{{ $answer->text_answer ?: __('exam.no_answer_given') }}</span>
                    @if($answer->text_answer)
                      <span class="tag">{{ $answer->is_correct ? __('exam.status_correct') : __('exam.status_wrong') }}</span>
                    @endif
                  </div>
                  <div class="rev-opt correct">
                    <b class="res-ans-label">{{ $answer->question->fillBlankAnswers->count() > 1 ? __('exam.accepted_answers') : __('exam.accepted_answer') }}:</b>
                    <span>{{ $answer->question->fillBlankAnswers->pluck('answer')->join(' / ') }}</span>
                    <span class="tag">{{ __('exam.correct_answer') }}</span>
                  </div>
                @else
                  @foreach($answer->question->options as $oi => $opt)
                    @php
                      $isSelected = in_array($opt->id, (array) $answer->selected_options);
                      $optClass = $opt->is_correct ? 'correct' : ($isSelected && !$opt->is_correct ? 'wrong' : '');
                      $tag = $opt->is_correct ? __('exam.correct_answer') : ($isSelected ? __('exam.your_answer') : '');
                    @endphp
                    <div class="rev-opt {{ $optClass }}">
                      <b class="mono res-opt-letter">{{ $letters[$oi] }}</b>
                      <span>{!! $opt->content !!}</span>
                      @if($tag)<span class="tag">{{ $tag }}</span>@endif
                    </div>
                  @endforeach
                @endif
                @if($answer->question->explanation)
                  <div class="rev-exp">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></svg>
                    <div><b>{{ __('exam.explanation_label') }}</b> {!! $answer->question->explanation !!}</div>
                  </div>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Retry confirmation modal --}}
@if(!$attempt->is_passed)
<div id="retryModal" class="q-modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="q-modal">
    <div class="q-modal-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 005.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 013.51 15"/></svg>
    </div>
    <h3 class="q-modal-title">{{ __('exam.try_again_heading') }}</h3>
    <p class="q-modal-body">{{ __('exam.try_again_body', ['quiz' => $attempt->quiz->title]) }}</p>
    <div class="q-modal-actions">
      <button class="btn btn-ghost" onclick="document.getElementById('retryModal').classList.remove('open')">{{ __('exam.cancel') }}</button>
      <a href="{{ route('attempt.start', $attempt->quiz->slug) }}" class="btn btn-accent">{{ __('exam.yes_retry') }}</a>
    </div>
  </div>
</div>
<style>
.q-modal-backdrop {
  display: none; position: fixed; inset: 0; background: rgba(15,10,30,.55);
  backdrop-filter: blur(4px); z-index: 9999;
  align-items: center; justify-content: center;
}
.q-modal-backdrop.open { display: flex; }
.q-modal {
  background: #fff; border-radius: 20px; padding: 36px 32px 28px;
  max-width: 400px; width: calc(100% - 32px); text-align: center;
  box-shadow: 0 24px 60px rgba(0,0,0,.18);
  animation: modalPop .22s cubic-bezier(.34,1.56,.64,1);
}
@keyframes modalPop { from { transform: scale(.88); opacity: 0; } }
.q-modal-icon {
  width: 60px; height: 60px; border-radius: 50%;
  background: #fef3c7; color: #d97706;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 16px;
}
.q-modal-title { font-size: 20px; font-weight: 700; margin: 0 0 8px; color: #1e1b4b; }
.q-modal-body  { font-size: 15px; color: #6b7280; margin: 0 0 24px; line-height: 1.55; }
.q-modal-actions { display: flex; gap: 10px; justify-content: center; }
.q-modal-actions .btn { min-width: 110px; justify-content: center; }
</style>
@endif

@push('scripts')
<script>
'use strict';
const pct = {{ $attempt->percentage }};
const isPassed = {{ $attempt->is_passed ? 'true' : 'false' }};
const C = 2 * Math.PI * 84;
const ring = document.getElementById('scoreRingFill');
ring.style.strokeDasharray = C;
ring.style.strokeDashoffset = C;

// Animate ring and counter over 2 seconds
const duration = 2000;
const start = performance.now();
function animateScore(ts) {
  const elapsed = ts - start;
  const progress = Math.min(elapsed / duration, 1);
  // Ease out cubic
  const eased = 1 - Math.pow(1 - progress, 3);
  const current = Math.round(eased * pct);
  if (document.getElementById('scoreNum')) document.getElementById('scoreNum').textContent = current;
  ring.style.strokeDashoffset = C * (1 - (eased * pct / 100));
  if (progress < 1) {
    requestAnimationFrame(animateScore);
  } else {
    if (isPassed) {
      // Confetti burst on pass
      confetti({ particleCount: 120, spread: 70, origin: { y: 0.5 }, colors: ['#6C2E63', '#E0A431', '#22C55E', '#fff'] });
      setTimeout(() => confetti({ particleCount: 60, spread: 120, origin: { y: 0.3 } }), 400);
    }
  }
}
requestAnimationFrame(animateScore);
</script>
@endpush
@endsection
