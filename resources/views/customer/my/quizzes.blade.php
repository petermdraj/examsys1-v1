@extends('layouts.app')
@section('title', __('common.my_quizzes_heading') . ' — Quizora')

@push('styles')
<style>
.myq-page-head{margin-bottom:28px;}
.myq-eyebrow{margin-bottom:6px;}
.myq-h1{font-size:32px;font-weight:700;}
.myq-card{padding:20px;margin-bottom:12px;display:flex;align-items:center;gap:16px;}
.myq-cover-img{width:64px;height:64px;border-radius:10px;object-fit:cover;flex:none;}
.myq-cover-placeholder{width:64px;height:64px;border-radius:10px;background:var(--brand-primary-soft);display:flex;align-items:center;justify-content:center;flex:none;}
.myq-info{flex:1;min-width:0;}
.myq-title-link{font-weight:600;text-decoration:none;color:var(--text);}
.myq-meta{display:flex;align-items:center;gap:10px;margin-top:4px;flex-wrap:wrap;}
.myq-cat-badge{font-size:12px;font-weight:500;color:var(--brand-primary);background:var(--brand-primary-soft);padding:2px 8px;border-radius:20px;}
.myq-meta-text{font-size:13px;}
.myq-result{margin-top:6px;font-size:12px;font-weight:500;}
.myq-actions{flex:none;display:flex;align-items:center;gap:8px;}
.myq-btn-sm{padding:8px 16px;font-size:13px;}
.myq-btn-ghost-sm{padding:8px 14px;font-size:13px;}
.myq-empty{padding:48px;text-align:center;}
.myq-empty-icon{font-size:48px;margin-bottom:16px;}
.myq-empty-h3{font-size:20px;font-weight:600;margin-bottom:8px;}
.myq-empty-sub{margin-bottom:20px;}
@media(max-width:560px){
  .myq-h1{font-size:24px;}
  .myq-card{flex-wrap:wrap;align-items:flex-start;}
  .myq-info{min-width:calc(100% - 80px);flex-basis:calc(100% - 80px);}
  .myq-actions{width:100%;border-top:1px solid var(--border);padding-top:10px;margin-top:4px;gap:8px;}
  .myq-btn-sm,.myq-btn-ghost-sm{flex:1;text-align:center;justify-content:center;}
}
</style>
@endpush

@section('content')
<div class="student-layout">
  @include('partials.student-nav')

  <div class="student-content">
    <div class="myq-page-head">
      <div class="eyebrow myq-eyebrow">{{ __('common.your_library') }}</div>
      <h1 class="myq-h1">{{ __('common.my_quizzes_heading') }}</h1>
    </div>

    @forelse($enrollments as $enrollment)
      @php
        $quiz    = $enrollment->quiz;
        $attempt = $latestAttempts[$quiz->id] ?? null;
      @endphp

      <div class="card myq-card">
        {{-- Cover --}}
        @if($quiz->cover_image)
          <img src="{{ Storage::url($quiz->cover_image) }}" alt="" class="myq-cover-img">
        @else
          <div class="myq-cover-placeholder">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--brand-primary)" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
        @endif

        {{-- Info --}}
        <div class="myq-info">
          <a href="{{ route('quizzes.show', $quiz->slug) }}" class="myq-title-link">{{ $quiz->title }}</a>
          <div class="myq-meta">
            @if($quiz->category)
              <span class="myq-cat-badge">{{ $quiz->category->name }}</span>
            @endif
            <span class="muted myq-meta-text">{{ $quiz->questions_count }} {{ __('common.quiz_questions') }}</span>
            <span class="muted myq-meta-text">{{ __('common.enrolled_on', ['date' => $enrollment->enrolled_at->format('d M Y')]) }}</span>
          </div>

          {{-- Previous result --}}
          @if($attempt && $attempt->status === 'completed')
            @php $statusLabel = $attempt->is_passed ? __('common.passed_label') : __('common.failed_label'); @endphp
            <div class="myq-result" style="color:{{ $attempt->is_passed ? 'var(--success)' : 'var(--danger)' }};">
              {{ __('common.last_attempt_result', ['pct' => $attempt->percentage, 'status' => $statusLabel]) }}
            </div>
          @endif
        </div>

        {{-- CTA --}}
        <div class="myq-actions">
          @if(!$attempt || $attempt->status === 'completed' || in_array($attempt->status, ['abandoned','timed_out']))
            <a href="{{ route('quizzes.show', $quiz->slug) }}" class="btn btn-primary myq-btn-sm">
              {{ $attempt && $attempt->status === 'completed' ? __('common.retake_btn') : __('common.start_btn') }}
            </a>
            @if($attempt && $attempt->status === 'completed')
              <a href="{{ route('attempt.result', $attempt) }}" class="btn btn-ghost myq-btn-ghost-sm">{{ __('common.result_btn') }}</a>
            @endif
          @elseif($attempt->status === 'in_progress')
            <a href="{{ route('attempt.show', $attempt) }}" class="btn btn-primary myq-btn-sm">{{ __('common.continue_btn') }}</a>
          @endif
        </div>
      </div>
    @empty
      <div class="card myq-empty">
        <div class="myq-empty-icon">🎓</div>
        <h3 class="myq-empty-h3">{{ __('common.no_quizzes_heading') }}</h3>
        <p class="muted myq-empty-sub">{{ __('common.no_quizzes_body') }}</p>
        <a href="{{ route('quizzes.index') }}" class="btn btn-primary">{{ __('common.discover_quizzes_btn') }}</a>
      </div>
    @endforelse

    <div class="pager">{{ $enrollments->links() }}</div>
  </div>
</div>
@endsection
