@extends('layouts.app')
@section('title', __('auth.login_title'))
@push('styles')
<style>
.lgn-page-wrap { min-height:60vh;display:flex;align-items:center;justify-content:center;padding:40px 20px; }
.lgn-card      { width:100%;max-width:420px;padding:40px; }
.lgn-header    { text-align:center;margin-bottom:32px; }
.lgn-title     { font-family:var(--font-display);font-size:28px;font-weight:700; }
.lgn-alert     { background:var(--danger-soft);color:var(--danger);padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px; }
.lgn-field     { margin-bottom:16px; }
.lgn-field-pw  { margin-bottom:24px; }
.lgn-footer    { text-align:center;margin-top:20px;font-size:14px;color:var(--text-muted); }
.lgn-footer-link { color:var(--brand-primary);font-weight:600; }
.lgn-subhead     { margin-top:8px; }
</style>
@endpush
@section('content')
<div class="lgn-page-wrap">
  <div class="card lgn-card">
    <div class="lgn-header">
      <div class="lgn-title">{{ __('auth.login_heading') }}</div>
      <div class="muted lgn-subhead">{{ __('auth.login_subheading') }}</div>
    </div>
    @if(config('examsys.demo_mode'))
    @php
      $demoCreds = [
        ['label'=>'Admin',   'email'=>'admin@quiz.com','password'=>'password','color'=>'#7c3aed'],
        ['label'=>'Creator', 'email'=>'priya@demo.quiz',  'password'=>'password','color'=>'#0ea5e9'],
        ['label'=>'Customer','email'=>'alice@demo.quiz',   'password'=>'password','color'=>'#22c55e'],
      ];
    @endphp
    <div style="margin-bottom:20px;border:1px solid rgba(124,58,237,.2);border-radius:10px;overflow:hidden;font-size:13px">
      <div style="background:rgba(124,58,237,.1);padding:7px 12px;font-size:10.5px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#7c3aed;display:flex;align-items:center;gap:5px">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z"/></svg>
        Demo credentials
      </div>
      @foreach($demoCreds as $dc)
      <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 12px;{{ !$loop->last ? 'border-bottom:1px solid #f3f0ff;' : '' }}background:#faf8ff">
        <div style="display:flex;align-items:center;gap:8px">
          <span style="background:{{ $dc['color'] }}18;color:{{ $dc['color'] }};border:1px solid {{ $dc['color'] }}30;border-radius:5px;padding:2px 7px;font-size:10px;font-weight:700">{{ $dc['label'] }}</span>
          <span style="color:#4b5563;font-family:monospace;font-size:12px">{{ $dc['email'] }}</span>
          <span style="color:#9ca3af">·</span>
          <span style="color:#9ca3af;font-family:monospace;font-size:12px">{{ $dc['password'] }}</span>
        </div>
        <button type="button"
          onclick="document.getElementById('email').value='{{ $dc['email'] }}';document.getElementById('password').value='{{ $dc['password'] }}';"
          style="font-size:11px;font-weight:600;color:{{ $dc['color'] }};background:{{ $dc['color'] }}12;border:1px solid {{ $dc['color'] }}25;border-radius:6px;padding:3px 9px;cursor:pointer">Use →</button>
      </div>
      @endforeach
    </div>
    @endif

    @if($errors->loginBag->any())
      <div class="lgn-alert">
        {{ $errors->loginBag->first() }}
      </div>
    @endif
    @if(session('error'))
      <div class="lgn-alert">
        {{ session('error') }}
      </div>
    @endif
    <form method="POST" action="{{ route('login.post') }}">@csrf
      <div class="field lgn-field">
        <label for="email">{{ __('auth.login_email_label') }}</label>
        <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="{{ __('auth.login_email_placeholder') }}">
      </div>
      <div class="field lgn-field-pw">
        <label for="password">{{ __('auth.login_password_label') }}</label>
        <input class="input" type="password" id="password" name="password" required placeholder="{{ __('auth.login_password_placeholder') }}">
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">{{ __('auth.login_submit') }}</button>
    </form>
    <p class="lgn-footer">
      {{ __('auth.login_no_account') }} <a href="{{ route('register') }}" class="lgn-footer-link">{{ __('auth.login_signup_link') }}</a>
    </p>
  </div>
</div>
@endsection
