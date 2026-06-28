@extends('layouts.app')
@section('title', __('common.certificates_my_heading') . ' — ' . config('app.name'))

@push('styles')
<style>
.myc-page-head{margin-bottom:28px;}
.myc-eyebrow{margin-bottom:6px;}
.myc-h1{font-size:32px;font-weight:700;}
.myc-card{padding:20px;margin-bottom:12px;display:flex;align-items:center;gap:16px;}
.myc-icon{width:52px;height:52px;border-radius:12px;background:var(--warning-soft,#fef3c7);display:flex;align-items:center;justify-content:center;flex:none;}
.myc-info{flex:1;min-width:0;}
.myc-title{font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.myc-meta{display:flex;align-items:center;gap:12px;margin-top:4px;flex-wrap:wrap;}
.myc-date{font-size:13px;}
.myc-pct{font-size:13px;font-weight:600;color:var(--success);}
.myc-actions{flex:none;display:flex;align-items:center;gap:8px;}
.myc-btn-sm{padding:7px 14px;font-size:13px;}
.myc-svg-icon{margin-right:4px;}
.myc-empty{padding:48px;text-align:center;}
.myc-empty-icon{font-size:48px;margin-bottom:16px;}
.myc-empty-h3{font-size:20px;font-weight:600;margin-bottom:8px;}
.myc-empty-sub{margin-bottom:20px;}
@media(max-width:560px){
  .myc-h1{font-size:24px;}
  .myc-card{flex-wrap:wrap;align-items:flex-start;}
  .myc-info{min-width:calc(100% - 68px);flex-basis:calc(100% - 68px);}
  .myc-actions{width:100%;border-top:1px solid var(--border);padding-top:10px;margin-top:4px;gap:8px;}
  .myc-btn-sm{flex:1;text-align:center;justify-content:center;}
}
</style>
@endpush

@section('content')
<div class="student-layout">
  @include('partials.student-nav')

  <div class="student-content">
    <div class="myc-page-head">
      <div class="eyebrow myc-eyebrow">{{ __('common.certificates_achievements') }}</div>
      <h1 class="myc-h1">{{ __('common.certificates_my_heading') }}</h1>
    </div>

    @forelse($certificates as $cert)
      <div class="card myc-card">
        {{-- Badge icon --}}
        <div class="myc-icon">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--warning,#f59e0b)" stroke-width="2"><circle cx="12" cy="9" r="5"/><path d="M9 13l-1 7 4-2 4 2-1-7"/></svg>
        </div>

        {{-- Info --}}
        <div class="myc-info">
          <div class="myc-title">{{ $cert->quiz->title }}</div>
          <div class="myc-meta">
            <span class="muted myc-date">{{ __('common.certificates_issued', ['date' => $cert->issued_at->format('d M Y')]) }}</span>
            @if($cert->attempt && $cert->attempt->percentage !== null)
              <span class="myc-pct">{{ __('common.certificates_passed_pct', ['pct' => $cert->attempt->percentage]) }}</span>
            @endif
          </div>
        </div>

        {{-- Actions --}}
        <div class="myc-actions">
          <a href="{{ route('certificate.verify', $cert->uuid) }}" target="_blank"
             class="btn btn-ghost myc-btn-sm"
             title="{{ __('common.certificates_view_btn') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="myc-svg-icon"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
            {{ __('common.certificates_view_btn') }}
          </a>
          @if($cert->attempt_id)
            <a href="{{ route('certificate.download', $cert->attempt_id) }}"
               class="btn btn-primary myc-btn-sm"
               title="{{ __('common.certificates_download_btn') }}">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="myc-svg-icon"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
              {{ __('common.certificates_download_btn') }}
            </a>
          @endif
        </div>
      </div>
    @empty
      <div class="card myc-empty">
        <div class="myc-empty-icon">🏆</div>
        <h3 class="myc-empty-h3">{{ __('common.certificates_none_heading') }}</h3>
        <p class="muted myc-empty-sub">{{ __('common.certificates_none_body') }}</p>
        <a href="{{ route('my.quizzes') }}" class="btn btn-primary">{{ __('common.certificates_go_quizzes') }}</a>
      </div>
    @endforelse

    <div class="pager">{{ $certificates->links() }}</div>
  </div>
</div>
@endsection
