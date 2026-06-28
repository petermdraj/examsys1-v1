@extends('layouts.app')
@section('title', __('auth.otp_title') . ' — ' . $platformSettings->app_name)
@push('styles')
<style>
.otp-page-wrap  { min-height:65vh;display:flex;align-items:center;justify-content:center;padding:40px 20px; }
.otp-card       { width:100%;max-width:420px;padding:40px;text-align:center; }
.otp-icon-ring  { width:64px;height:64px;background:color-mix(in srgb,var(--brand-primary) 10%,white);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px; }
.otp-title      { font-family:var(--font-display);font-size:24px;font-weight:700;margin-bottom:8px; }
.otp-sub        { font-size:14px;margin-bottom:28px;line-height:1.6; }
.otp-alert-err  { background:var(--danger-soft,#fef2f2);color:var(--danger,#dc2626);padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;text-align:left; }
.otp-alert-ok   { background:#f0fdf4;color:#16a34a;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px; }
.otp-input-wrap { margin-bottom:20px; }
.otp-input      { width:100%;text-align:center;font-size:32px;font-weight:700;letter-spacing:10px;font-family:monospace;
                  border:2px solid var(--border);border-radius:12px;padding:16px 12px;
                  color:var(--text);background:#fff;outline:none;box-sizing:border-box;
                  transition:border-color .15s; }
.otp-resend-row { margin-top:24px;font-size:14px;color:var(--text-muted); }
.otp-resend-form { display:inline; }
.otp-resend-btn { background:none;border:none;padding:0;color:var(--brand-primary);font-weight:600;font-size:14px;cursor:pointer;text-decoration:underline; }
.otp-wrong-row  { margin-top:12px;font-size:13px; }
.otp-wrong-link { color:var(--text-muted);text-decoration:none; }
.otp-logout-form { display:none; }
.otp-email       { color:var(--text); }
</style>
@endpush
@section('content')
<div class="otp-page-wrap">
  <div class="card otp-card">

    {{-- Icon --}}
    <div class="otp-icon-ring">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--brand-primary)" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3-8.59A2 2 0 0 1 3.62 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.5 16v.92h.5z"/><path d="M14.05 2a9 9 0 0 1 8 7.94"/><path d="M14.05 6A5 5 0 0 1 18 10"/></svg>
    </div>

    <h1 class="otp-title">{{ __('auth.otp_heading') }}</h1>
    <p class="muted otp-sub">
      {{ __('auth.otp_subheading') }}<br>
      <strong class="otp-email">{{ session('otp_email') }}</strong>
    </p>

    {{-- Alerts --}}
    @if($errors->any())
      <div class="otp-alert-err">
        @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
      </div>
    @endif
    @if(session('status') === 'otp-resent')
      <div class="otp-alert-ok">
        {{ __('auth.otp_code_resent') }}
      </div>
    @endif

    {{-- OTP form --}}
    <form method="POST" action="{{ route('otp.verify.post') }}">
      @csrf
      <div class="otp-input-wrap">
        <input
          type="text"
          name="otp"
          inputmode="numeric"
          pattern="[0-9]{6}"
          maxlength="6"
          autofocus
          autocomplete="one-time-code"
          placeholder="{{ __('auth.otp_placeholder') }}"
          class="otp-input"
          onfocus="this.style.borderColor='var(--brand-primary)'"
          onblur="this.style.borderColor='var(--border)'"
          oninput="this.value=this.value.replace(/\D/g,'')"
        >
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">{{ __('auth.otp_submit') }}</button>
    </form>

    {{-- Resend --}}
    <div class="otp-resend-row">
      {{ __('auth.otp_didnt_receive') }}
      <form method="POST" action="{{ route('otp.resend') }}" class="otp-resend-form">
        @csrf
        <button type="submit" class="otp-resend-btn">
          {{ __('auth.otp_resend') }}
        </button>
      </form>
    </div>

    {{-- Wrong account? --}}
    <div class="otp-wrong-row">
      <a href="{{ route('logout') }}"
         onclick="event.preventDefault();document.getElementById('logout-form-otp').submit();"
         class="otp-wrong-link">
        {{ __('auth.otp_wrong_account') }}
      </a>
      <form id="logout-form-otp" method="POST" action="{{ route('logout') }}" class="otp-logout-form">@csrf</form>
    </div>

  </div>
</div>
@endsection
