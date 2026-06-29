@extends('layouts.app')
@section('title', __('auth.register_heading') . ' — ' . $platformSettings->app_name)
@push('styles')
<style>
.reg-page-wrap   { min-height:60vh;display:flex;align-items:center;justify-content:center;padding:40px 20px; }
.reg-card        { width:100%;max-width:440px;padding:40px; }
.reg-header      { text-align:center;margin-bottom:32px; }
.reg-title       { font-family:var(--font-display);font-size:28px;font-weight:700; }
.reg-alert       { background:var(--danger-soft);color:var(--danger);padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px; }
.reg-field       { margin-bottom:16px; }
.reg-field-last  { margin-bottom:24px; }
.reg-creator-box { margin-bottom:20px;padding:14px 16px;background:var(--surface-soft,#f5f4ff);border:1.5px solid var(--border);border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:16px;cursor:pointer; }
.reg-creator-label-title { font-weight:600;font-size:14px;color:var(--text); }
.reg-creator-label-desc  { font-size:12.5px;color:var(--text-muted);margin-top:2px; }
.reg-toggle      { position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;cursor:pointer; }
.reg-toggle-input { opacity:0;width:0;height:0;position:absolute; }
.reg-toggle-track { position:absolute;inset:0;border-radius:12px;background:#CBD5E1;transition:.2s; }
.reg-toggle-thumb { position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:.2s; }
.reg-divider     { display:flex;align-items:center;gap:12px;margin:20px 0; }
.reg-divider-hr  { flex:1;border:none;border-top:1px solid var(--border); }
.reg-divider-lbl { font-size:13px; }
.reg-google-btn  { display:flex;align-items:center;justify-content:center;gap:10px;padding:11px; }
.reg-footer      { text-align:center;margin-top:20px;font-size:14px;color:var(--text-muted); }
.reg-footer-link { color:var(--brand-primary);font-weight:600; }
.reg-subhead     { margin-top:8px; }
</style>
@endpush
@section('content')
<div class="reg-page-wrap">
  <div class="card reg-card">
    <div class="reg-header">
      <div class="reg-title">{{ __('auth.register_heading') }}</div>
      <div class="muted reg-subhead">{{ __('auth.register_subheading') }}</div>
    </div>

    @if($errors->any())
      <div class="reg-alert">
        @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
      </div>
    @endif

    @if(session('error'))
      <div class="reg-alert">
        {{ session('error') }}
      </div>
    @endif

    <form method="POST" action="{{ route('register.post') }}">@csrf
      <div class="field reg-field">
        <label>{{ __('auth.register_name_label') }}</label>
        <input class="input" type="text" name="name" value="{{ old('name') }}" required placeholder="{{ __('auth.register_name_placeholder') }}">
      </div>
      <div class="field reg-field">
        <label>{{ __('auth.register_email_label') }}</label>
        <input class="input" type="email" name="email" value="{{ old('email') }}" required placeholder="{{ __('auth.login_email_placeholder') }}">
      </div>
      <div class="field reg-field">
        <label>{{ __('auth.register_password_label') }}</label>
        <input class="input" type="password" name="password" required placeholder="{{ __('auth.register_password_placeholder') }}">
      </div>

      {{-- Lecturer registration only — students are added by admin --}}
      @if($platformSettings->lecturer_registration_open)
      <input type="hidden" name="role" value="lecturer">
      <div class="reg-creator-box" style="border-color:var(--brand-primary);background:color-mix(in srgb,var(--brand-primary) 8%,white);cursor:default;">
        <div>
          <div class="reg-creator-label-title">{{ __('auth.register_lecturer_only_title') }}</div>
          <div class="reg-creator-label-desc">{{ __('auth.register_lecturer_only_desc') }}</div>
        </div>
      </div>
      @endif

      {{-- Hidden field so unchecked checkbox still submits "customer" --}}
      @if(!$platformSettings->lecturer_registration_open)
        {{-- Registration page should not render when closed; kept for safety --}}
      @endif

      <div class="field reg-field-last">
        <label>{{ __('auth.register_confirm_label') }}</label>
        <input class="input" type="password" name="password_confirmation" required placeholder="{{ __('auth.register_confirm_placeholder') }}">
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">{{ __('auth.register_submit') }}</button>
    </form>

    {{-- Google / Social login --}}
    @if($platformSettings->allow_social_login)
      <div class="reg-divider">
        <hr class="reg-divider-hr">
        <span class="muted reg-divider-lbl">{{ __('auth.or_divider') }}</span>
        <hr class="reg-divider-hr">
      </div>
      <a href="{{ route('auth.google') }}"
         class="btn btn-ghost btn-block reg-google-btn">
        <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
        {{ __('auth.continue_with_google') }}
      </a>
    @endif

    <p class="reg-footer">
      {{ __('auth.register_have_account') }} <a href="{{ route('login') }}" class="reg-footer-link">{{ __('auth.register_login_link') }}</a>
    </p>
  </div>
</div>
@endsection
