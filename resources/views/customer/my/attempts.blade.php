@extends('layouts.app')
@section('title', __('exam.my_attempts_heading') . ' — ' . config('app.name'))

@push('styles')
<style>
.mya-page-head{margin-bottom:24px;}
.mya-eyebrow{margin-bottom:6px;}
.mya-h1{font-size:32px;font-weight:700;}
.mya-filters{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;}
.mya-card{padding:16px 20px;margin-bottom:10px;}
.mya-card-row{display:flex;align-items:flex-start;gap:16px;}
.mya-info{flex:1;min-width:0;}
.mya-title{font-weight:600;}
.mya-meta{display:flex;align-items:center;gap:10px;margin-top:4px;flex-wrap:wrap;}
.mya-cat-badge{font-size:11px;font-weight:500;color:var(--brand-primary);background:var(--brand-primary-soft);padding:2px 8px;border-radius:20px;}
.mya-date{font-size:12px;}
.mya-score-box{text-align:right;flex:none;}
.mya-score-num{font-size:20px;font-weight:700;font-family:var(--font-mono,monospace);}
.mya-score-label{font-size:11px;font-weight:600;}
.mya-btn-sm{padding:7px 14px;font-size:13px;flex:none;}
.mya-in-progress-badge{background:var(--warning-soft);color:var(--warning);flex:none;}
.mya-abandoned-badge{background:var(--surface-2);color:var(--text-muted);flex:none;}
.mya-score-bar-wrap{margin-top:12px;}
.mya-score-track{height:6px;background:var(--surface-2);border-radius:3px;overflow:hidden;position:relative;}
.mya-score-threshold{position:absolute;top:-2px;bottom:-2px;width:2px;background:var(--text-muted);border-radius:1px;}
.mya-score-labels{display:flex;justify-content:space-between;margin-top:4px;}
.mya-score-tick{font-size:11px;color:var(--text-muted);}
.mya-empty{padding:48px;text-align:center;}
.mya-empty-icon{font-size:40px;margin-bottom:12px;}
.mya-empty-btn{margin-top:16px;}
@media(max-width:560px){
  .mya-h1{font-size:24px;}
  .mya-card-row{flex-wrap:wrap;gap:10px;}
  .mya-score-box{width:100%;text-align:left;display:flex;align-items:center;gap:10px;order:3;}
  .mya-score-label{margin-left:2px;}
  .mya-btn-sm{width:100%;justify-content:center;order:4;}
  .mya-in-progress-badge,.mya-abandoned-badge{order:3;}
}
</style>
@endpush

@section('content')
<div class="student-layout">
  @include('partials.student-nav')

  <div class="student-content">
    <div class="mya-page-head">
      <div class="eyebrow mya-eyebrow">{{ __('exam.history_eyebrow') }}</div>
      <h1 class="mya-h1">{{ __('exam.my_attempts_heading') }}</h1>
    </div>

    {{-- Filter tabs --}}
    <div class="mya-filters">
      @foreach(['all' => __('exam.filter_all'), 'completed' => __('exam.filter_completed'), 'active' => __('exam.filter_in_progress'), 'missed' => __('exam.filter_missed')] as $key => $label)
        <a href="{{ route('my.attempts', ['status' => $key]) }}"
           style="padding:7px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid {{ $status === $key ? 'var(--brand-primary)' : 'var(--border)' }};background:{{ $status === $key ? 'var(--brand-primary)' : 'transparent' }};color:{{ $status === $key ? '#fff' : 'var(--text-muted)' }};transition:all .15s;">
          {{ $label }}
        </a>
      @endforeach
    </div>

    @forelse($attempts as $attempt)
      <div class="card mya-card">
        <div class="mya-card-row">
          <div class="mya-info">
            <div class="mya-title">{{ $attempt->quiz->title }}</div>
            <div class="mya-meta">
              @if($attempt->quiz->category)
                <span class="mya-cat-badge">{{ $attempt->quiz->category->name }}</span>
              @endif
              <span class="muted mya-date">{{ $attempt->started_at->format('d M Y, h:i A') }}</span>
            </div>
          </div>

          @if($attempt->status === 'completed')
            <div class="mya-score-box">
              <div class="mya-score-num" style="color:{{ $attempt->is_passed ? 'var(--success)' : 'var(--danger)' }}">{{ $attempt->percentage }}%</div>
              <div class="mya-score-label" style="color:{{ $attempt->is_passed ? 'var(--success)' : 'var(--danger)' }}">{{ $attempt->is_passed ? __('exam.passed_label') : __('exam.failed_label') }}</div>
            </div>
            <a href="{{ route('attempt.result', $attempt) }}" class="btn btn-ghost mya-btn-sm">{{ __('exam.view_result') }}</a>
          @elseif($attempt->status === 'in_progress')
            <span class="badge mya-in-progress-badge">{{ __('exam.in_progress_badge') }}</span>
            <a href="{{ route('attempt.show', $attempt) }}" class="btn btn-primary mya-btn-sm">{{ __('exam.continue') }}</a>
          @elseif(in_array($attempt->status, ['abandoned','timed_out']))
            <span class="badge mya-abandoned-badge">{{ $attempt->status === 'timed_out' ? __('exam.timed_out_badge') : __('exam.abandoned_badge') }}</span>
            <a href="{{ route('quizzes.show', $attempt->quiz->slug) }}" class="btn btn-ghost mya-btn-sm">{{ __('exam.retake') }}</a>
          @endif
        </div>

        {{-- Score bar (completed only) --}}
        @if($attempt->status === 'completed')
          @php $pct = min(100, max(0, $attempt->percentage ?? 0)); $pass = $attempt->quiz->pass_percentage ?? 60; @endphp
          <div class="mya-score-bar-wrap">
            <div class="mya-score-track">
              <div style="position:absolute;left:0;top:0;height:100%;width:{{ $pct }}%;background:{{ $pct >= $pass ? 'var(--success)' : 'var(--danger)' }};border-radius:3px;transition:width .6s;"></div>
              {{-- Pass threshold marker --}}
              <div class="mya-score-threshold" style="left:{{ $pass }}%;"></div>
            </div>
            <div class="mya-score-labels">
              <span class="mya-score-tick">0</span>
              <span class="mya-score-tick">{{ __('exam.pass_threshold', ['pct' => $pass]) }}</span>
              <span class="mya-score-tick">100</span>
            </div>
          </div>
        @endif
      </div>
    @empty
      <div class="card mya-empty">
        <div class="mya-empty-icon">📝</div>
        <p class="muted">{{ __('exam.no_attempts_found') }}</p>
        <a href="{{ route('quizzes.index') }}" class="btn btn-primary mya-empty-btn">{{ __('exam.browse_quizzes') }}</a>
      </div>
    @endforelse

    <div class="pager">{{ $attempts->links() }}</div>
  </div>
</div>
@endsection
