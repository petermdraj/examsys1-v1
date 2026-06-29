@extends('layouts.app')
@section('title', __('common.my_favourites_heading') . ' — ' . config('app.name'))

@push('styles')
<style>
.myf-page-head{margin-bottom:28px;}
.myf-eyebrow{margin-bottom:6px;}
.myf-h1{font-size:32px;font-weight:700;}
.myf-card{padding:0;margin-bottom:12px;overflow:hidden;display:flex;align-items:stretch;gap:0;position:relative;}
.myf-cover{width:80px;flex:none;background-size:cover;background-position:center;}
.myf-info{flex:1;min-width:0;padding:16px 20px;}
.myf-badges{display:flex;align-items:flex-start;gap:8px;flex-wrap:wrap;margin-bottom:4px;}
.myf-cat-badge{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--brand-primary);background:var(--brand-soft,#ede9fe);padding:2px 8px;border-radius:20px;}
.myf-paid-badge{font-size:11px;font-weight:700;color:#059669;background:#d1fae5;padding:2px 8px;border-radius:20px;}
.myf-title{font-size:16px;font-weight:600;color:var(--text);text-decoration:none;display:block;margin-bottom:4px;line-height:1.35;}
.myf-meta{display:flex;align-items:center;gap:16px;flex-wrap:wrap;}
.myf-meta-item{font-size:13px;display:flex;align-items:center;gap:4px;}
.myf-meta-saved{font-size:12px;}
.myf-actions{flex:none;display:flex;align-items:center;gap:8px;padding:16px 16px 16px 0;}
.myf-btn{padding:8px 18px;font-size:13px;}
.myf-empty{padding:56px;text-align:center;}
.myf-empty-icon{font-size:52px;margin-bottom:16px;}
.myf-empty-h3{font-size:20px;font-weight:600;margin-bottom:8px;}
.myf-empty-sub{margin-bottom:24px;}
.myf-accent-strip{width:6px;flex:none;}
@media(max-width:560px){
  .myf-h1{font-size:24px;}
  .myf-card{flex-wrap:wrap;}
  .myf-cover{width:100%;height:120px;background-size:cover;}
  .myf-info{width:100%;padding:12px 16px 0;}
  .myf-actions{width:100%;border-top:1px solid var(--border);padding:12px 16px;gap:8px;}
  .myf-btn{flex:1;text-align:center;justify-content:center;}
  .myf-accent-strip{display:none;}
}
</style>
@endpush

@section('content')
<div class="student-layout">
  @include('partials.student-nav')

  <div class="student-content">
    <div class="myf-page-head">
      <div class="eyebrow myf-eyebrow">{{ __('common.saved_eyebrow') }}</div>
      <h1 class="myf-h1">{{ __('common.my_favourites_heading') }}</h1>
    </div>

    @forelse($favourites as $fav)
      @php $quiz = $fav->quiz; $idx = ($loop->index % 9) + 1; @endphp
      <div class="card myf-card">

        {{-- Colour accent strip --}}
        <div class="cov-{{ $idx }} myf-accent-strip"></div>

        {{-- Cover thumbnail (if set) --}}
        @if($quiz->cover_image)
          <div class="myf-cover" style="background-image:url('{{ Storage::url($quiz->cover_image) }}');"></div>
        @endif

        {{-- Info --}}
        <div class="myf-info">
          <div class="myf-badges">
            @if($quiz->category)
              <span class="myf-cat-badge">{{ $quiz->category->name }}</span>
            @endif
            @if($quiz->price > 0)
              <span class="myf-paid-badge">{{ $sym }}{{ number_format($quiz->price, 0) }}</span>
            @endif
          </div>

          <a href="{{ route('quizzes.show', $quiz->slug) }}"
             class="myf-title"
             onmouseover="this.style.color='var(--brand-primary)'"
             onmouseout="this.style.color='var(--text)'">
            {{ $quiz->title }}
          </a>

          <div class="myf-meta">
            <span class="muted myf-meta-item">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
              {{ $quiz->total_questions }} {{ __('common.quiz_questions') }}
            </span>
            @if($quiz->duration_minutes)
              <span class="muted myf-meta-item">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                {{ $quiz->duration_minutes }} {{ __('common.quiz_min') }}
              </span>
            @endif
            <span class="muted myf-meta-item">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
              {{ number_format($quiz->total_attempts) }} {{ __('common.quiz_attempts') }}
            </span>
            <span class="muted myf-meta-saved">{{ __('common.saved_on', ['date' => $fav->created_at->format('d M Y')]) }}</span>
          </div>
        </div>

        {{-- Actions --}}
        <div class="myf-actions">
          <a href="{{ route('quizzes.show', $quiz->slug) }}" class="btn btn-primary myf-btn">
            {{ __('common.view_quiz_btn') }}
          </a>
          {{-- Un-favourite --}}
          <livewire:toggle-favourite :quizId="$quiz->id" :key="'fav-'.$quiz->id" />
        </div>
      </div>
    @empty
      <div class="card myf-empty">
        <div class="myf-empty-icon">🤍</div>
        <h3 class="myf-empty-h3">{{ __('common.no_favourites_heading') }}</h3>
        <p class="muted myf-empty-sub">{{ __('common.no_favourites_body') }}</p>
        <a href="{{ route('quizzes.index') }}" class="btn btn-primary">{{ __('common.browse_quizzes_btn') }}</a>
      </div>
    @endforelse

    <div class="pager">{{ $favourites->links() }}</div>
  </div>
</div>
@endsection
