@php
$quiz = $attempt->quiz;
$letters = ['A','B','C','D','E','F'];
@endphp

<div
  wire:poll.10s="refreshAttemptStatus"
  x-data="examPanel({
    questions: @js($questions),
    initialAnswers: @js($answers),
    initialMarked: @js($markedForReview),
    durationSeconds: {{ $durationSeconds }},
    startedAt: {{ $attempt->started_at->valueOf() }},
    saveUrl: '{{ route('attempt.answer', $attempt) }}',
    violationUrl: '{{ route('attempt.violation', $attempt) }}',
    proctoringEnabled: @js($quiz->proctoring_enabled),
    isPaused: @js($attempt->status === 'paused'),
    csrf: '{{ csrf_token() }}'
  })"
  class="exam"
>

  @if($attempt->status === 'paused')
  <div class="exam-paused-overlay" style="position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.92);display:flex;align-items:center;justify-content:center;color:#fff;text-align:center;padding:24px;">
    <div>
      <div style="font-size:48px;margin-bottom:16px;">⏸</div>
      <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:8px;">{{ __('exam.exam_paused_by_admin') }}</h2>
      <p style="opacity:.8;max-width:360px;">{{ __('exam.exam_paused_wait') }}</p>
    </div>
  </div>
  @endif

  {{-- TOP BAR --}}
  <div class="exam-top">
    <div class="exam-brand"><span class="m">{{ strtoupper(substr(config('app.name'), 0, 1)) }}</span> {{ strtoupper(config('app.name')) }}</div>
    <div class="exam-title" x-text="'{{ $quiz->title }}'"></div>

    <div class="exam-timer">
      <span class="exp-time-lbl">{{ __('exam.time_left') }}</span>
      <span class="timer-clock"
        :class="{ 'amber': timerRemaining <= 300 && timerRemaining > 60, 'red': timerRemaining <= 60 }"
        x-text="{{ $durationSeconds }} > 0 ? timerDisplay : '∞'"></span>

      {{-- Save status indicator --}}
      <span class="save-indicator"
        :class="saveStatus"
        x-show="saveStatus !== 'idle'"
        x-transition>
        <span x-show="saveStatus === 'saving'">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="exp-spin"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" opacity=".3"/><path d="M21 12A9 9 0 0012 3"/></svg>
          {{ __('exam.saving') }}
        </span>
        <span x-show="saveStatus === 'saved'">{{ __('exam.saved') }}</span>
        <span x-show="saveStatus === 'error'">{{ __('exam.not_saved') }}</span>
      </span>

      <button
        type="button"
        class="btn-end"
        :disabled="saveStatus === 'error'"
        :title="saveStatus === 'error' ? '{{ __('exam.save_failed_connection') }}' : ''"
        @click="confirmSubmit"
      >{{ __('exam.end_test') }}</button>
    </div>

    {{-- Mobile: palette toggle --}}
    <button class="exam-palette-toggle" @click="paletteOpen = true" aria-label="{{ __('exam.open_question_palette') }}">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
      Questions <span x-text="questions.length"></span>
    </button>
  </div>

  <div class="exam-body">

    {{-- QUESTION AREA --}}
    <div class="exam-main" id="questionArea">
      @foreach($questions as $qi => $question)
        <div class="question-slide" id="q-{{ $qi }}" x-show="curQ === {{ $qi }}" x-cloak>

          <div class="exam-q-meta">
            <span class="qn">{{ __('exam.question_of', ['current' => $qi + 1, 'total' => count($questions)]) }}</span>
            <span class="sep">·</span>
            <span>{{ $question['marks'] }} {{ $question['marks'] != 1 ? __('exam.mark_plural') : __('exam.mark_singular') }}</span>
            @if($question['negative_marks'] > 0)
              <span class="sep">·</span>
              <span class="neg">{{ __('exam.negative_wrong', ['marks' => $question['negative_marks']]) }}</span>
            @endif
            <span class="sep">·</span>
            <span>{{ str_replace('_', ' ', $question['type']) }}</span>
          </div>

          <div class="exam-question">{!! $question['content'] !!}</div>

          <div class="exam-opts" id="opts-{{ $qi }}">
            @if(in_array($question['type'], ['mcq_single', 'true_false']))
              @foreach($question['options'] as $oi => $opt)
                <div class="exam-opt"
                     :class="{ sel: (answers['{{ $question['id'] }}'] || []).includes('{{ $opt['id'] }}') }"
                     @click="selectSingle({{ $qi }}, '{{ $question['id'] }}', '{{ $opt['id'] }}')"
                     class="exp-opt-cursor">
                  <span class="mk">{{ $letters[$oi] }}</span>
                  <span>{!! $opt['content'] !!}</span>
                </div>
              @endforeach
            @elseif($question['type'] === 'mcq_multiple')
              @foreach($question['options'] as $oi => $opt)
                <div class="exam-opt"
                     :class="{ sel: (answers['{{ $question['id'] }}'] || []).includes('{{ $opt['id'] }}') }"
                     @click="selectMultiple({{ $qi }}, '{{ $question['id'] }}', '{{ $opt['id'] }}')"
                     class="exp-opt-cursor">
                  <span class="mk">{{ $letters[$oi] }}</span>
                  <span>{!! $opt['content'] !!}</span>
                </div>
              @endforeach
            @elseif($question['type'] === 'fill_blank')
              <div class="exp-fill-wrap">
                <input
                  type="text"
                  class="exam-fill-input"
                  placeholder="{{ __('exam.fill_blank_placeholder') }}"
                  :value="textAnswers['{{ $question['id'] }}'] || ''"
                  @input.debounce.800ms="saveFillBlank({{ $qi }}, '{{ $question['id'] }}', $event.target.value)"
                />
              </div>
            @endif
          </div>

          @if($question['hint'])
            <div x-data="{ showHint: false }" class="exp-hint-wrap">
              <button class="exam-hint" @click="showHint = !showHint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18h6M10 21h4M12 3a6 6 0 00-4 10c.7.7 1 1.5 1 2h6c0-.5.3-1.3 1-2a6 6 0 00-4-10z"/></svg>
                <span x-text="showHint ? '{{ __('exam.hide_hint') }}' : '{{ __('exam.show_hint') }}'"></span>
              </button>
              <div x-show="showHint" class="exp-hint-body">
                {{ $question['hint'] }}
              </div>
            </div>
          @endif
        </div>
      @endforeach
    </div>

    {{-- PALETTE SIDEBAR --}}
    <aside class="exam-side">

      {{-- Filter tabs --}}
      <div class="palette-tabs">
        <button class="pal-tab" :class="{ active: paletteFilter === 'all' }"       @click="paletteFilter = 'all'">{{ __('exam.filter_all') }}</button>
        <button class="pal-tab" :class="{ active: paletteFilter === 'answered' }"  @click="paletteFilter = 'answered'">{{ __('exam.filter_answered') }}</button>
        <button class="pal-tab" :class="{ active: paletteFilter === 'unanswered' }" @click="paletteFilter = 'unanswered'">{{ __('exam.filter_unanswered') }}</button>
        <button class="pal-tab" :class="{ active: paletteFilter === 'marked' }"    @click="paletteFilter = 'marked'">{{ __('exam.filter_marked') }}</button>
      </div>

      {{-- Palette grid --}}
      <div class="palette" id="palette">
        @foreach($questions as $qi => $q)
          <button
            class="cell"
            :class="{ current: curQ === {{ $qi }} }"
            :data-st="getCellState('{{ $q['id'] }}')"
            x-show="paletteFilter === 'all'
              || (paletteFilter === 'answered'   && isAnswered('{{ $q['id'] }}'))
              || (paletteFilter === 'unanswered' && !isAnswered('{{ $q['id'] }}'))
              || (paletteFilter === 'marked'     && markedForReview.includes('{{ $q['id'] }}'))"
            @click="gotoQuestion({{ $qi }})"
            title="{{ __('exam.question_number', ['number' => $qi + 1]) }}"
          >
            {{ $qi + 1 }}
            {{-- Icon overlays for a11y --}}
            <span class="cell-icon cell-icon-check"  x-show="isAnswered('{{ $q['id'] }}') && !markedForReview.includes('{{ $q['id'] }}')" aria-hidden="true">
              <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <span class="cell-icon cell-icon-dot"    x-show="isVisited({{ $qi }}) && !isAnswered('{{ $q['id'] }}') && !markedForReview.includes('{{ $q['id'] }}')" aria-hidden="true">
              <svg width="7" height="7" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg>
            </span>
            <span class="cell-icon cell-icon-mark"   x-show="markedForReview.includes('{{ $q['id'] }}')" aria-hidden="true">
              <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M5 3v18l7-5 7 5V3z"/></svg>
            </span>
          </button>
        @endforeach
      </div>

      {{-- Legend --}}
      <div class="palette-head exp-legend-head">{{ __('exam.legend') }}</div>
      <div class="legend">
        <div class="legend-row"><span class="legend-sw exp-sw-answered"></span> {{ __('exam.legend_answered') }} <b x-text="countByState('answered')"></b></div>
        <div class="legend-row"><span class="legend-sw exp-sw-visited"></span> {{ __('exam.legend_not_answered') }} <b x-text="countByState('visited')"></b></div>
        <div class="legend-row"><span class="legend-sw exp-sw-review"></span> {{ __('exam.legend_marked_for_review') }} <b x-text="markedForReview.length"></b></div>
        <div class="legend-row"><span class="legend-sw exp-sw-unvisited"></span> {{ __('exam.legend_not_visited') }} <b x-text="countByState('unvisited')"></b></div>
      </div>

      {{-- Controls --}}
      <div class="exam-controls">
        <button class="btn-exam review" :class="{ on: markedForReview.includes(questions[curQ]?.id) }" @click="toggleMark()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 3v18l7-5 7 5V3z"/></svg>
          {{ __('exam.mark_for_review') }}
        </button>
        <button class="btn-exam primary" @click="saveAndNext()">{{ __('exam.save_and_next') }}</button>
        <div class="exam-nav-row">
          <button class="btn-exam" @click="navQ(-1)">{{ __('exam.prev') }}</button>
          <button class="btn-exam" @click="navQ(1)">{{ __('exam.next') }}</button>
        </div>
      </div>
    </aside>
  </div>

  {{-- Mobile bottom action bar --}}
  <div class="exam-mobile-bar">
    <button class="btn-exam" @click="navQ(-1)">{{ __('exam.prev') }}</button>
    <button class="btn-exam review" :class="{ on: markedForReview.includes(questions[curQ]?.id) }" @click="toggleMark()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 3v18l7-5 7 5V3z"/></svg>
    </button>
    <button class="btn-exam primary exp-save-next-mobile" @click="saveAndNext()">{{ __('exam.save_and_next') }}</button>
    <button class="btn-exam" @click="navQ(1)">{{ __('exam.next') }}</button>
  </div>

  {{-- Mobile bottom-sheet palette --}}
  <div class="palette-sheet-backdrop" x-show="paletteOpen" @click="paletteOpen = false" x-transition.opacity class="exp-hidden"></div>
  <div class="palette-sheet" x-show="paletteOpen" x-transition:enter="slide-up" x-transition:leave="slide-down" class="exp-hidden">
    <div class="palette-sheet-header">
      <span>{{ __('exam.question_palette') }}</span>
      <button @click="paletteOpen = false" aria-label="{{ __('exam.close') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="palette-tabs">
      <button class="pal-tab" :class="{ active: paletteFilter === 'all' }"       @click="paletteFilter = 'all'">{{ __('exam.filter_all') }}</button>
      <button class="pal-tab" :class="{ active: paletteFilter === 'answered' }"  @click="paletteFilter = 'answered'">{{ __('exam.filter_done') }}</button>
      <button class="pal-tab" :class="{ active: paletteFilter === 'unanswered' }" @click="paletteFilter = 'unanswered'">{{ __('exam.filter_skip') }}</button>
      <button class="pal-tab" :class="{ active: paletteFilter === 'marked' }"    @click="paletteFilter = 'marked'">★</button>
    </div>
    <div class="palette exp-palette-sheet-grid">
      @foreach($questions as $qi => $q)
        <button
          class="cell"
          :class="{ current: curQ === {{ $qi }} }"
          :data-st="getCellState('{{ $q['id'] }}')"
          x-show="paletteFilter === 'all'
            || (paletteFilter === 'answered'   && isAnswered('{{ $q['id'] }}'))
            || (paletteFilter === 'unanswered' && !isAnswered('{{ $q['id'] }}'))
            || (paletteFilter === 'marked'     && markedForReview.includes('{{ $q['id'] }}'))"
          @click="gotoQuestion({{ $qi }}); paletteOpen = false"
        >
          {{ $qi + 1 }}
          <span class="cell-icon cell-icon-check"  x-show="isAnswered('{{ $q['id'] }}') && !markedForReview.includes('{{ $q['id'] }}')" aria-hidden="true">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
          </span>
          <span class="cell-icon cell-icon-mark"   x-show="markedForReview.includes('{{ $q['id'] }}')" aria-hidden="true">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M5 3v18l7-5 7 5V3z"/></svg>
          </span>
        </button>
      @endforeach
    </div>
  </div>

  {{-- Submit confirmation modal --}}
  <div class="modal-backdrop" x-show="showSubmitConfirm" class="exp-hidden" x-transition.opacity @click.self="showSubmitConfirm = false">
    <div class="modal-box" x-transition>
      <h3 class="exp-modal-title">{{ __('exam.submit_exam_heading') }}</h3>
      <p class="exp-modal-body">
        <span x-text="countByState('unvisited') + countByState('visited')"></span> {{ __('exam.submit_unanswered_warning', ['count' => '']) }}
      </p>
      <div class="exp-modal-actions">
        <button class="btn-exam" @click="showSubmitConfirm = false">{{ __('exam.cancel') }}</button>
        <button class="btn-end" @click="doSubmit()" :disabled="submitting" x-text="submitting ? '{{ __('exam.submitting') }}' : '{{ __('exam.submit_exam') }}'"></button>
      </div>
    </div>
  </div>

  {{-- Save-failed submit blocked toast --}}
  <div class="exam-toast error" x-show="saveBlockToast" x-transition class="exp-hidden">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
    {{ __('exam.save_failed_connection') }}
  </div>

  <script>
'use strict';
function examPanel({ questions, initialAnswers, initialMarked, durationSeconds, startedAt, saveUrl, violationUrl, proctoringEnabled, isPaused, csrf }) {
  return {
    questions,
    curQ: 0,
    answers: initialAnswers || {},
    textAnswers: {},
    markedForReview: initialMarked || [],
    visited: [0],
    paletteFilter: 'all',
    paletteOpen: false,
    saveStatus: 'idle',
    saveBlockToast: false,
    showSubmitConfirm: false,
    submitting: false,
    timerRemaining: durationSeconds,
    timerInterval: null,
    autoSaveInterval: null,
    lastViolationAt: 0,

    get timerDisplay() {
      const h = String(Math.floor(this.timerRemaining / 3600)).padStart(2, '0');
      const m = String(Math.floor((this.timerRemaining % 3600) / 60)).padStart(2, '0');
      const s = String(this.timerRemaining % 60).padStart(2, '0');
      return `${h}:${m}:${s}`;
    },

    init() {
      if (durationSeconds > 0) {
        const key = `exam_timer_${location.pathname}`;
        const stored = localStorage.getItem(key);
        if (stored) {
          const { remaining, savedAt } = JSON.parse(stored);
          const elapsed = Math.floor((Date.now() - savedAt) / 1000);
          this.timerRemaining = Math.max(0, remaining - elapsed);
        }

        this.timerInterval = setInterval(() => {
          if (this.timerRemaining <= 0) {
            clearInterval(this.timerInterval);
            this.doSubmit();
            return;
          }
          this.timerRemaining--;
          localStorage.setItem(key, JSON.stringify({ remaining: this.timerRemaining, savedAt: Date.now() }));
        }, 1000);
      }

      this.autoSaveInterval = setInterval(() => {
        if (!isPaused) this.saveCurrentAnswer();
      }, 30000);

      if (proctoringEnabled && violationUrl && !isPaused) {
        const logViolation = (type, message) => {
          const now = Date.now();
          if (now - this.lastViolationAt < 2000) return;
          this.lastViolationAt = now;
          fetch(violationUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ type, message }),
          }).catch(() => {});
        };

        document.addEventListener('visibilitychange', () => {
          if (document.hidden) {
            logViolation('tab_switch', 'User switched browser tab or minimized window');
          }
        });

        window.addEventListener('blur', () => {
          logViolation('window_blur', 'Browser window lost focus');
        });
      }
    },

    isAnswered(qid) {
      const sel = this.answers[qid] || [];
      return sel.length > 0 || (this.textAnswers[qid] || '').trim().length > 0;
    },

    isVisited(qi) {
      return this.visited.includes(qi);
    },

    getCellState(qid) {
      const qi = this.questions.findIndex(q => q.id === qid);
      const answered = this.isAnswered(qid);
      const marked = this.markedForReview.includes(qid);
      if (answered && marked) return 'answered-review';
      if (answered) return 'answered';
      if (marked) return 'review';
      if (this.isVisited(qi)) return 'visited';
      return 'unvisited';
    },

    countByState(state) {
      return this.questions.filter(q => {
        const s = this.getCellState(q.id);
        if (state === 'visited') return s === 'visited';
        if (state === 'unvisited') return s === 'unvisited';
        if (state === 'answered') return s === 'answered' || s === 'answered-review';
        return false;
      }).length;
    },

    gotoQuestion(qi) {
      this.saveCurrentAnswer();
      this.curQ = qi;
      if (!this.visited.includes(qi)) this.visited.push(qi);
    },

    selectSingle(qi, qid, optId) {
      this.answers[qid] = [optId];
      this.saveAnswer(qid);
    },

    selectMultiple(qi, qid, optId) {
      const current = [...(this.answers[qid] || [])];
      const idx = current.indexOf(optId);
      if (idx === -1) current.push(optId);
      else current.splice(idx, 1);
      this.answers[qid] = current;
      this.saveAnswer(qid);
    },

    saveFillBlank(qi, qid, val) {
      this.textAnswers[qid] = val;
      this.saveAnswer(qid);
    },

    saveCurrentAnswer() {
      const q = this.questions[this.curQ];
      if (q) this.saveAnswer(q.id);
    },

    async saveAnswer(qid) {
      this.saveStatus = 'saving';
      try {
        const res = await fetch(saveUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
          body: JSON.stringify({
            question_id: qid,
            selected_options: this.answers[qid] || [],
            text_answer: this.textAnswers[qid] || null,
            is_marked_for_review: this.markedForReview.includes(qid),
          }),
        });
        if (!res.ok) throw new Error('Network error');
        this.saveStatus = 'saved';
        setTimeout(() => { if (this.saveStatus === 'saved') this.saveStatus = 'idle'; }, 2500);
      } catch {
        this.saveStatus = 'error';
      }
    },

    toggleMark() {
      const qid = this.questions[this.curQ]?.id;
      if (!qid) return;
      const idx = this.markedForReview.indexOf(qid);
      if (idx === -1) this.markedForReview.push(qid);
      else this.markedForReview.splice(idx, 1);
      this.saveAnswer(qid);
    },

    saveAndNext() {
      this.saveCurrentAnswer();
      if (this.curQ < this.questions.length - 1) this.gotoQuestion(this.curQ + 1);
    },

    navQ(dir) {
      const n = Math.min(this.questions.length - 1, Math.max(0, this.curQ + dir));
      this.gotoQuestion(n);
    },

    confirmSubmit() {
      if (this.saveStatus === 'error') {
        this.saveBlockToast = true;
        setTimeout(() => { this.saveBlockToast = false; }, 4000);
        return;
      }
      this.showSubmitConfirm = true;
    },

    async doSubmit() {
      if (this.submitting) return;
      this.submitting = true;
      this.showSubmitConfirm = false;
      clearInterval(this.timerInterval);
      clearInterval(this.autoSaveInterval);
      localStorage.removeItem(`exam_timer_${location.pathname}`);

      const elapsedSeconds = durationSeconds > 0
        ? durationSeconds - this.timerRemaining
        : Math.floor((Date.now() - startedAt) / 1000);

      try {
        const res = await fetch('{{ route('attempt.submit', $attempt) }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ elapsed_seconds: elapsedSeconds }),
        });
        const data = await res.json();
        if (data.redirect) {
          window.location.href = data.redirect;
        } else {
          window.location.href = '{{ route('attempt.result', $attempt) }}';
        }
      } catch {
        window.location.href = '{{ route('attempt.result', $attempt) }}';
      }
    },
  };
}
  </script>
  <style>
[x-cloak] { display: none !important; }
@keyframes spin { to { transform: rotate(360deg); } }

/* exp- utility classes */
.exp-hidden           { display:none; }
.exp-time-lbl         { font-family:var(--font-mono);font-size:11px;color:var(--exam-text-muted);letter-spacing:.04em; }
.exp-spin             { animation:spin 1s linear infinite; }
.exp-opt-cursor       { cursor:pointer; }
.exp-hint-wrap        { margin-top:16px; }
.exp-hint-body        { margin-top:10px;padding:12px 16px;background:var(--exam-surface-2);border-radius:8px;font-size:14px;color:var(--exam-text-muted); }
.exp-legend-head      { margin:16px 0 8px;font-size:12px;color:var(--exam-text-muted); }
.exp-sw-answered      { background:var(--pal-answered); }
.exp-sw-visited       { background:var(--pal-visited); }
.exp-sw-review        { background:var(--pal-review); }
.exp-sw-unvisited     { background:var(--pal-unvisited); }
.exp-save-next-mobile { flex:2; }
.exp-palette-sheet-grid { padding:16px; }
.exp-modal-title      { margin:0 0 8px;font-size:18px; }
.exp-modal-body       { margin:0 0 20px;color:var(--exam-text-muted);font-size:14px; }
.exp-modal-actions    { display:flex;gap:10px;justify-content:flex-end; }
.exp-fill-wrap        { padding:8px 0; }

.save-indicator { font-size: 12px; font-family: var(--font-mono); display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; }
.save-indicator.saving { color: var(--exam-text-muted); }
.save-indicator.saved  { color: var(--success); }
.save-indicator.error  { color: var(--danger); }

.palette-tabs { display: flex; gap: 4px; margin-bottom: 12px; }
.pal-tab { font-size: 11px; font-family: var(--font-body); font-weight: 600; padding: 4px 10px; border-radius: 20px; border: 1.5px solid var(--exam-border); background: transparent; color: var(--exam-text-muted); cursor: pointer; transition: all .15s; }
.pal-tab.active { background: var(--brand-primary); border-color: var(--brand-primary); color: #fff; }

.cell { position: relative; }
.cell-icon { position: absolute; bottom: 3px; right: 3px; display: flex; align-items: center; justify-content: center; pointer-events: none; }
.cell-icon-check { color: rgba(255,255,255,.9); }
.cell-icon-dot   { color: rgba(255,255,255,.8); }
.cell-icon-mark  { color: rgba(255,255,255,.9); }

.exam-palette-toggle { display: none; }
@media (max-width: 768px) {
  .exam-side { display: none; }
  .exam-palette-toggle { display: inline-flex; align-items: center; gap: 6px; background: var(--exam-surface-2); border: 1.5px solid var(--exam-border); border-radius: 8px; padding: 6px 12px; font-size: 13px; color: var(--exam-text); cursor: pointer; margin-left: auto; }
}

.palette-sheet-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 100; }
.palette-sheet { position: fixed; bottom: 0; left: 0; right: 0; background: var(--exam-surface); border-radius: 20px 20px 0 0; z-index: 101; max-height: 70vh; overflow-y: auto; }
.palette-sheet-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px 8px; font-weight: 700; font-size: 15px; color: var(--exam-text); border-bottom: 1px solid var(--exam-border); }
.palette-sheet-header button { background: none; border: none; cursor: pointer; color: var(--exam-text-muted); padding: 4px; }

.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200; display: flex; align-items: center; justify-content: center; }
.modal-box { background: var(--exam-surface); border-radius: 16px; padding: 28px; max-width: 420px; width: calc(100% - 40px); }

.exam-fill-input { width: 100%; padding: 12px 16px; background: var(--exam-surface-2); border: 1.5px solid var(--exam-border); border-radius: 10px; color: var(--exam-text); font-family: var(--font-body); font-size: 16px; outline: none; }
.exam-fill-input:focus { border-color: var(--brand-primary); }

.exam-toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 500; z-index: 300; white-space: nowrap; }
.exam-toast.error { background: var(--danger-soft, #fef2f2); color: var(--danger); border: 1px solid var(--danger); }

.btn-end:disabled { opacity: .45; cursor: not-allowed; }

.slide-up-enter-active { transition: transform .3s ease, opacity .3s; }
.slide-up-leave-active { transition: transform .25s ease, opacity .25s; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); opacity: 0; }
  </style>

</div>
