@extends('layouts.app')
@section('title', __('common.my_dashboard_title') . ' — ' . config('app.name'))

@push('styles')
<style>
.myd-page-head{margin-bottom:28px;}
.myd-eyebrow{margin-bottom:6px;}
.myd-h1{font-size:32px;font-weight:700;}
.myd-stats{margin-bottom:32px;}
.myd-stat-card{padding:24px;}
.myd-stat-eyebrow{margin-bottom:8px;}
.myd-stat-num{font-family:var(--font-display);font-size:36px;font-weight:700;}
.myd-stat-brand{color:var(--brand-primary);}
.myd-stat-warning{color:var(--warning);}
.myd-section-head{margin-bottom:16px;}
.myd-section-h2{font-size:18px;font-weight:600;}
.myd-section-link{font-size:14px;font-weight:600;}
.myd-attempt-card{padding:16px 20px;margin-bottom:10px;display:flex;align-items:center;gap:16px;}
.myd-attempt-info{flex:1;min-width:0;}
.myd-attempt-title{font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.myd-attempt-date{font-size:13px;margin-top:3px;}
.myd-score-box{text-align:right;flex:none;}
.myd-score-num{font-size:18px;font-weight:700;}
.myd-score-label{font-size:11px;}
.myd-btn-sm{padding:7px 14px;font-size:13px;flex:none;}
.myd-in-progress-badge{background:var(--warning-soft);color:var(--warning);flex:none;}
.myd-status-badge{background:var(--surface-2);color:var(--text-muted);flex:none;}
.myd-empty{padding:40px;text-align:center;}
.myd-empty-icon{font-size:40px;margin-bottom:12px;}
.myd-empty-sub{margin-bottom:16px;}
@media(max-width:560px){
  .myd-h1{font-size:24px;}
  .myd-stat-num{font-size:28px;}
  .myd-attempt-card{flex-wrap:wrap;gap:10px;}
  .myd-score-box{width:100%;text-align:left;display:flex;align-items:center;gap:12px;order:3;}
  .myd-score-label{margin-left:4px;}
  .myd-btn-sm{width:100%;justify-content:center;order:4;}
  .myd-in-progress-badge{order:3;}
  .myd-status-badge{order:3;}
}
</style>
@endpush

@section('content')
<div class="student-layout">
  @include('partials.student-nav')

  <div class="student-content">
    <div class="myd-page-head">
      <div class="eyebrow myd-eyebrow">{{ __('common.welcome_back') }}</div>
      <h1 class="myd-h1">{{ $user->name }}</h1>
    </div>

    {{-- Stats --}}
    <div class="grid-3 myd-stats">
      <div class="card myd-stat-card">
        <div class="eyebrow myd-stat-eyebrow">{{ __('common.quizzes_enrolled') }}</div>
        <div class="myd-stat-num myd-stat-brand">{{ $enrolledCount }}</div>
      </div>
      <div class="card myd-stat-card">
        <div class="eyebrow myd-stat-eyebrow">{{ __('common.pass_rate') }}</div>
        <div class="myd-stat-num" style="color:{{ $passRate === null ? 'var(--text-muted)' : ($passRate >= 60 ? 'var(--success)' : 'var(--danger)') }};">
          {{ $passRate !== null ? $passRate . '%' : '—' }}
        </div>
      </div>
      <div class="card myd-stat-card">
        <div class="eyebrow myd-stat-eyebrow">{{ __('common.certificates_earned') }}</div>
        <div class="myd-stat-num myd-stat-warning">{{ $certsCount }}</div>
      </div>
    </div>

    {{-- Recent attempts --}}
    <div>
      <div class="section-head myd-section-head">
        <h2 class="myd-section-h2">{{ __('common.recent_attempts') }}</h2>
        <a class="sec myd-section-link" href="{{ route('my.attempts') }}">{{ __('common.view_all_arrow') }}</a>
      </div>

      @forelse($attempts as $attempt)
        <div class="card myd-attempt-card">
          <div class="myd-attempt-info">
            <div class="myd-attempt-title">{{ $attempt->quiz->title }}</div>
            <div class="muted myd-attempt-date">{{ $attempt->started_at->diffForHumans() }}</div>
          </div>

          @if($attempt->status === 'completed')
            <div class="myd-score-box">
              <div class="myd-score-num" style="color:{{ $attempt->is_passed ? 'var(--success)' : 'var(--danger)' }}">{{ $attempt->percentage }}%</div>
              <div class="muted myd-score-label">{{ $attempt->is_passed ? __('quiz.result_passed') : __('quiz.result_failed') }}</div>
            </div>
            <a href="{{ route('attempt.result', $attempt) }}" class="btn btn-ghost myd-btn-sm">{{ __('common.result') }}</a>
          @elseif($attempt->status === 'in_progress')
            <span class="badge myd-in-progress-badge">{{ __('exam.in_progress_badge') }}</span>
            <a href="{{ route('attempt.show', $attempt) }}" class="btn btn-primary myd-btn-sm">{{ __('common.continue') }}</a>
          @else
            <span class="badge myd-status-badge">{{ ucfirst(str_replace('_', ' ', $attempt->status)) }}</span>
          @endif
        </div>
      @empty
        <div class="card myd-empty">
          <div class="myd-empty-icon">📝</div>
          <p class="muted myd-empty-sub">{{ __('common.havent_attempted_yet') }}</p>
          <a href="{{ route('quizzes.index') }}" class="btn btn-primary">{{ __('exam.browse_quizzes') }}</a>
        </div>
      @endforelse
    </div>
  </div>
</div>
@endsection
