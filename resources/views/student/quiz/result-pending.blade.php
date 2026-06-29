@extends('layouts.app')
@section('title', __('exam.result_pending_title') . ' — ' . $attempt->quiz->title)

@section('content')
<div class="wrap" style="max-width:560px;margin:48px auto;">
  <div class="card" style="padding:40px 32px;text-align:center;">
    <div style="font-size:48px;margin-bottom:16px;">⏳</div>
    <div class="eyebrow" style="margin-bottom:8px;">{{ __('exam.attempt_complete') }}</div>
    <h1 style="font-size:1.75rem;font-weight:800;margin-bottom:12px;">{{ __('exam.result_pending_title') }}</h1>
    <p class="sec" style="font-size:1rem;line-height:1.6;margin-bottom:24px;">{{ __('exam.result_pending_body', ['quiz' => $attempt->quiz->title]) }}</p>
    <a href="{{ route('my.attempts') }}" class="btn btn-accent">{{ __('exam.view_my_attempts') }}</a>
  </div>
</div>
@endsection
