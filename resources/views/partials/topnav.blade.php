<style>
.tnav-logo{height:36px;width:auto;max-width:140px;object-fit:contain;display:block}
.tnav-links{display:flex;align-items:center;gap:4px;margin-left:24px}
@media(max-width:767px){.tnav-links{display:none;}}
.tnav-spacer{flex:1}
.tnav-right{flex:none;display:flex;align-items:center;gap:12px}
.tnav-avatar-accent{background:var(--brand-accent);color:var(--brand-primary-strong)}
.tnav-avatar-primary{background:var(--brand-primary)}
.tnav-form-inline{display:contents}
.tnav-logout-btn{border:none;background:none;width:100%;text-align:left;color:var(--danger)}
.tnav-search{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:6px 14px;transition:.15s;margin-left:16px}
.tnav-search:hover,.tnav-search:focus-within{background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.4)}
.tnav-search svg{width:15px;height:15px;flex:none;opacity:.8;color:var(--on-primary)}
.tnav-search input{border:none;background:transparent;outline:none;color:var(--on-primary);font-size:13.5px;width:180px}
.tnav-search input::placeholder{color:rgba(255,255,255,.55)}
@media(max-width:1024px){.tnav-search{display:none}}
</style>

<header class="topnav">
  <a href="{{ route('home') }}" class="brand">
    @if($platformSettings->app_logo)
      <img src="{{ Storage::url($platformSettings->app_logo) }}"
           alt="{{ $platformSettings->app_name }}"
           class="tnav-logo">
    @else
      <div class="brand-mark">{{ strtoupper(substr($platformSettings->app_name, 0, 1)) }}</div>
      <div class="brand-name">{{ $platformSettings->app_name }}</div>
    @endif
  </a>
  <nav class="topnav-links tnav-links">
    <a href="{{ route('quizzes.index') }}" class="nav-item-light">{{ __('common.nav_discover') }}</a>
    <a href="{{ route('categories.index') }}" class="nav-item-light">{{ __('common.pubbar_categories') }}</a>
    <a href="{{ route('for-creators') }}" class="nav-item-light">{{ __('common.nav_for_creators') }}</a>
    <a href="{{ route('pricing') }}" class="nav-item-light">{{ __('common.nav_pricing') }}</a>
  </nav>
  <form action="{{ route('quizzes.index') }}" method="GET" class="tnav-search topnav-links" role="search">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
    <input type="search" name="search" placeholder="{{ __('common.pubbar_search') }}" value="{{ request('search') }}" autocomplete="off">
  </form>
  <div class="tnav-spacer"></div>
  <div class="tnav-right">
    <!-- Language switcher -->
    @php
        $__settings       = rescue(fn () => app(\App\Settings\PlatformSettings::class), null, false);
        $__showFront      = $__settings?->show_switcher_front   ?? true;
        $__enabledLocales = $__settings?->enabled_locales       ?? ['en'];
        $__hiddenLocales  = $__settings?->hidden_locales        ?? [];
        $__extraLocales   = $__settings?->extra_locales         ?? [];
        $__extraFlags     = $__settings?->extra_locale_flags    ?? [];
        $__rtlLocales     = $__settings?->rtl_locales           ?? [];
        $__allLocales     = array_merge(config('app.available_locales', ['en' => 'English']), $__extraLocales);
        foreach ($__hiddenLocales as $__h) { unset($__allLocales[$__h]); }
        $__current        = app()->getLocale();
        $__visible        = array_filter($__allLocales, fn ($c) => in_array($c, $__enabledLocales), ARRAY_FILTER_USE_KEY);
        $__builtinFlags   = ['en'=>'🇬🇧','hi'=>'🇮🇳','de'=>'🇩🇪','fr'=>'🇫🇷','nl'=>'🇳🇱','da'=>'🇩🇰','no'=>'🇳🇴','sv'=>'🇸🇪','ja'=>'🇯🇵'];
        $__flags          = array_merge($__builtinFlags, $__extraFlags);
    @endphp
    @if($__showFront && count($__visible) > 1)
    <div class="relative" x-data="{ lsOpen: false }" @click.outside="lsOpen = false" style="position:relative;">
        <button @click="lsOpen=!lsOpen" type="button"
            style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.12);color:inherit;cursor:pointer;font-size:13px;font-weight:500;transition:.15s;"
            onmouseenter="this.style.background='rgba(255,255,255,.22)'" onmouseleave="this.style.background='rgba(255,255,255,.12)'"
        >
            <span>{{ $__flags[$__current] ?? '🌐' }}</span>
            <span style="max-width:60px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $__allLocales[$__current] ?? strtoupper($__current) }}</span>
            <svg style="width:12px;height:12px;opacity:.7;transition:.15s;" :style="lsOpen?'transform:rotate(180deg)':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="lsOpen"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            style="display:none;position:absolute;right:0;top:calc(100% + 6px);z-index:200;min-width:160px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.12);overflow:hidden;"
        >
            <form method="POST" action="{{ route('language.switch') }}" id="ls-front-form">
                @csrf
                <input type="hidden" name="locale" id="ls-front-input" value="{{ $__current }}">
            </form>
            @foreach($__visible as $code => $name)
            @php $isAct = $__current === $code; @endphp
            <button type="button"
                onclick="document.getElementById('ls-front-input').value='{{ $code }}';document.getElementById('ls-front-form').submit();"
                style="width:100%;display:flex;align-items:center;gap:10px;padding:9px 14px;border:none;background:{{ $isAct ? '#f3f4f6' : 'transparent' }};cursor:pointer;font-size:13.5px;font-weight:{{ $isAct ? '600' : '400' }};color:#374151;text-align:left;transition:.1s;"
                onmouseenter="this.style.background='#f9fafb'" onmouseleave="this.style.background='{{ $isAct ? '#f3f4f6' : 'transparent' }}'"
            >
                <span style="font-size:18px;line-height:1;width:22px;text-align:center;">{{ $__flags[$code] ?? '🌐' }}</span>
                <span style="flex:1;">{{ $name }}</span>
                @if($isAct)
                <svg style="width:14px;height:14px;color:#6366f1;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                @endif
            </button>
            @endforeach
        </div>
    </div>
    @endif

    @guest
      <a href="{{ route('login') }}" class="nav-item-light topnav-auth">{{ __('common.nav_login') }}</a>
      <a href="{{ route('register') }}" class="btn-nav-accent topnav-auth">{{ __('common.nav_signup') }}</a>
    @else
      <div class="usermenu" id="userMenu">
        <button class="user-btn" id="userBtn" onclick="document.getElementById('userPop').classList.toggle('show')">
          <span class="avatar tnav-avatar-accent">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</span>
          <span class="nm">{{ explode(' ', auth()->user()->name)[0] }}</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="user-pop" id="userPop">
          <div class="up-head">
            <span class="avatar avatar-lg tnav-avatar-primary">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</span>
            <div><div class="nm">{{ auth()->user()->name }}</div><div class="em">{{ auth()->user()->email }}</div></div>
          </div>
          <div class="up-sep"></div>
          @if(auth()->user()->role === 'super_admin')
            <a href="{{ url('/admin') }}" class="up-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg> {{ __('common.user_admin_panel') }}</a>
          @endif
          @if(in_array(auth()->user()->role, ['creator','super_admin']))
            <a href="{{ url('/creator') }}" class="up-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z"/></svg> {{ __('common.user_creator_dashboard') }}</a>
          @endif
          <a href="{{ route('my.dashboard') }}" class="up-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg> {{ __('common.user_my_dashboard') }}</a>
          <a href="{{ route('my.attempts') }}" class="up-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a9 9 0 109 9"/><path d="M12 3v9l6-3"/></svg> {{ __('common.user_my_attempts') }}</a>
          <a href="{{ route('my.certificates') }}" class="up-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="9" r="5"/><path d="M9 13l-1 7 4-2 4 2-1-7"/></svg> {{ __('common.user_certificates') }}</a>
          <a href="{{ route('profile.show') }}" class="up-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg> {{ __('common.user_account_settings') }}</a>
          <div class="up-sep"></div>
          <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="up-item danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 17l5-5-5-5M21 12H9M12 3H5a2 2 0 00-2 2v14a2 2 0 002 2h7"/></svg> {{ __('common.user_logout') }}</button>
          </form>
        </div>
      </div>
    @endguest

    {{-- Hamburger (mobile only) --}}
    <button class="nav-hamburger" id="navHamburger" aria-label="{{ __('common.nav_open_menu') }}" onclick="document.getElementById('mobileNav').classList.toggle('open');this.classList.toggle('active')">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

{{-- Mobile nav slide-down --}}
<div class="mobile-nav" id="mobileNav">
  <form action="{{ route('quizzes.index') }}" method="GET" style="padding:10px 16px 4px">
    <div class="tnav-search" style="display:flex;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:8px 14px;gap:8px;margin:0">
      <svg style="width:15px;height:15px;flex:none;opacity:.8;color:var(--on-primary)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
      <input type="search" name="search" placeholder="{{ __('common.pubbar_search') }}" value="{{ request('search') }}" autocomplete="off" style="border:none;background:transparent;outline:none;color:var(--on-primary);font-size:14px;width:100%">
    </div>
  </form>
  <a href="{{ route('quizzes.index') }}" class="mobile-nav-item">{{ __('common.nav_discover') }}</a>
  <a href="{{ route('categories.index') }}" class="mobile-nav-item">{{ __('common.pubbar_categories') }}</a>
  <a href="{{ route('for-creators') }}" class="mobile-nav-item">{{ __('common.nav_for_creators') }}</a>
  <a href="{{ route('pricing') }}" class="mobile-nav-item">{{ __('common.nav_pricing') }}</a>
  <div class="mobile-nav-sep"></div>
  @guest
    <a href="{{ route('login') }}" class="mobile-nav-item">{{ __('common.nav_login') }}</a>
    <a href="{{ route('register') }}" class="mobile-nav-item mobile-nav-cta">{{ __('common.nav_signup') }}</a>
  @else
    @if(in_array(auth()->user()->role, ['creator','super_admin']))
      <a href="{{ url('/creator') }}" class="mobile-nav-item">{{ __('common.user_creator_dashboard') }}</a>
    @endif
    <a href="{{ route('my.dashboard') }}" class="mobile-nav-item">{{ __('common.user_my_dashboard') }}</a>
    <form method="POST" action="{{ route('logout') }}" class="tnav-form-inline">@csrf
      <button type="submit" class="mobile-nav-item tnav-logout-btn">{{ __('common.user_logout') }}</button>
    </form>
  @endguest
</div>

<script>
'use strict';
document.addEventListener('click', function(e) {
  const ham = document.getElementById('navHamburger');
  const nav = document.getElementById('mobileNav');
  if (ham && nav && !ham.contains(e.target) && !nav.contains(e.target)) {
    nav.classList.remove('open');
    ham.classList.remove('active');
  }
});
</script>
