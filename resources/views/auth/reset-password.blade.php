@extends('layouts.app')
@section('title', __('auth.reset_btn') . ' — ' . $platformSettings->app_name)

@push('styles')
<style>
.auth-page-wrap{min-height:60vh;display:flex;align-items:center;justify-content:center;padding:40px 20px}
.auth-card{width:100%;max-width:440px;padding:40px}
.auth-header{text-align:center;margin-bottom:28px}
.auth-title{font-family:var(--font-display);font-size:26px;font-weight:700;color:var(--text-heading)}
.auth-subtitle{margin-top:8px;font-size:14px;color:var(--text-muted)}
.auth-alert-error{background:var(--danger-soft);color:var(--danger);border:1px solid var(--danger);padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px}
.auth-field{margin-bottom:20px}
.auth-footer{text-align:center;margin-top:20px;font-size:14px;color:var(--text-muted)}
.auth-footer-link{color:var(--brand-primary);font-weight:600}
</style>
@endpush

@section('content')
<div class="auth-page-wrap">
  <div class="card auth-card">
    <div class="auth-header">
      <div class="auth-title">{{ __('auth.reset_btn') }}</div>
      <div class="auth-subtitle">{{ __('auth.forgot_intro') }}</div>
    </div>

    @if($errors->any())
      <div class="auth-alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $request->route('token') }}">

      <div class="field auth-field">
        <label for="email">{{ __('auth.reset_email_label') }}</label>
        <input class="input" type="email" id="email" name="email"
               value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
               placeholder="{{ __('auth.reset_email_label') }}">
      </div>

      <div class="field auth-field">
        <label for="password">{{ __('auth.reset_password_input') }}</label>
        <input class="input" type="password" id="password" name="password"
               required autocomplete="new-password"
               placeholder="{{ __('auth.reset_password_input') }}">
      </div>

      <div class="field auth-field">
        <label for="password_confirmation">{{ __('auth.reset_confirm_input') }}</label>
        <input class="input" type="password" id="password_confirmation" name="password_confirmation"
               required autocomplete="new-password"
               placeholder="{{ __('auth.reset_confirm_input') }}">
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg">
        {{ __('auth.reset_btn') }}
      </button>
    </form>

    <p class="auth-footer">
      <a href="{{ route('login') }}" class="auth-footer-link">← {{ __('auth.login_heading') }}</a>
    </p>
  </div>
</div>
@endsection
