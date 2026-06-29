@extends('layouts.app')
@section('title', __('quiz.discover_title') . ' — ' . config('app.name'))
@section('content')
<div class="wrap" style="padding-top:40px;padding-bottom:60px;">
  <h1 style="font-size:32px;font-weight:700;margin-bottom:8px;">{{ __('quiz.discover_title') }}</h1>
  <p class="sec" style="margin-bottom:32px;">{{ __('quiz.discover_subtitle') }}</p>
  @livewire('quiz-search')
</div>
@endsection
