<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
@if($platformSettings->app_favicon)
<link rel="icon" href="{{ Storage::url($platformSettings->app_favicon) }}">
<link rel="shortcut icon" href="{{ Storage::url($platformSettings->app_favicon) }}">
@endif
@php
    $defaultTitle = trim($platformSettings->seo_title) ?: $platformSettings->app_name;
@endphp
<title>@yield('title', $defaultTitle)</title>
<meta name="description" content="@yield('meta_description', $platformSettings->seo_description)">
@hasSection('meta_keywords')
<meta name="keywords" content="@yield('meta_keywords')">
@elseif($platformSettings->seo_keywords)
<meta name="keywords" content="{{ $platformSettings->seo_keywords }}">
@endif
@if($platformSettings->og_image)
<meta property="og:image" content="@yield('og_image', $platformSettings->og_image)">
@endif
<meta property="og:title" content="@yield('title', $platformSettings->seo_title ?: $platformSettings->app_name)">
<meta property="og:description" content="@yield('meta_description', $platformSettings->seo_description)">
<meta property="og:type" content="@yield('og_type', 'website')">
@php
    // ── Typography settings ──────────────────────────────────────────────────
    $fontDisplay = $platformSettings->font_display   ?? 'Plus Jakarta Sans';
    $fontPrimary = $platformSettings->font_primary   ?? 'Inter';
    $fontSizeBase = $platformSettings->font_size_base ?? '16px';

    // Build the Google Fonts URL — collect unique families needed
    $googleFontsMap = [
        'Plus Jakarta Sans' => 'Plus+Jakarta+Sans:wght@400;500;600;700;800',
        'Inter'             => 'Inter:wght@400;500;600;700',
        'Sora'              => 'Sora:wght@400;500;600;700;800',
        'Space Grotesk'     => 'Space+Grotesk:wght@400;500;600;700',
        'Poppins'           => 'Poppins:wght@400;500;600;700',
        'Nunito'            => 'Nunito:wght@400;500;600;700;800',
        'Merriweather'      => 'Merriweather:ital,wght@0,400;0,700;1,400',
        'Playfair Display'  => 'Playfair+Display:ital,wght@0,400;0,700;1,400',
        'Outfit'            => 'Outfit:wght@400;500;600;700',
        'DM Sans'           => 'DM+Sans:wght@400;500;600;700',
        'IBM Plex Sans'     => 'IBM+Plex+Sans:wght@400;500;600;700',
        'Source Sans 3'     => 'Source+Sans+3:wght@400;500;600;700',
        'Lato'              => 'Lato:wght@400;700',
        'Rubik'             => 'Rubik:wght@400;500;600;700',
    ];
    $familiesNeeded = array_unique([$fontDisplay, $fontPrimary]);
    $googleFamilyParams = collect($familiesNeeded)
        ->filter(fn($f) => isset($googleFontsMap[$f]))
        ->map(fn($f) => 'family=' . $googleFontsMap[$f])
        ->implode('&');
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?' . $googleFamilyParams . '&display=swap';
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="{{ $googleFontsUrl }}" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
@stack('styles')
{{-- Dynamic brand colors + typography from PlatformSettings — must come AFTER app.css to override static defaults --}}
@php
    $primary = $platformSettings->primary_color ?: '#6C2E63';
    $accent  = $platformSettings->accent_color  ?: '#E0A431';
    // Darken primary ~20% for "strong" shade (navbar/footer bg)
    $primaryStrong = \App\Helpers\ColorHelper::darken($primary, 20);
    // Light tint for soft shade
    $primarySoft   = \App\Helpers\ColorHelper::lighten($primary, 45);
    $accentStrong  = \App\Helpers\ColorHelper::darken($accent, 20);
    $accentSoft    = \App\Helpers\ColorHelper::lighten($accent, 45);
    // Choose on-primary text color (white or dark) based on luminance
    $onPrimary = \App\Helpers\ColorHelper::contrastColor($primary);
@endphp
<style>
:root {
  --brand-primary:        {{ $primary }};
  --brand-primary-strong: {{ $primaryStrong }};
  --brand-primary-soft:   {{ $primarySoft }};
  --brand-accent:         {{ $accent }};
  --brand-accent-strong:  {{ $accentStrong }};
  --brand-accent-soft:    {{ $accentSoft }};
  --on-primary:           {{ $onPrimary }};
  --font-display:         '{{ $fontDisplay }}', sans-serif;
  --font-primary:         '{{ $fontPrimary }}', sans-serif;
  --font-size-base:       {{ $fontSizeBase }};
}
html { font-size: {{ $fontSizeBase }}; }
body { font-family: var(--font-primary); }
h1, h2, h3, h4, h5, h6,
.font-display { font-family: var(--font-display); }
</style>
</head>
<body id="app-body">

<x-filament-impersonate::banner />

@include('partials.topnav')

{{-- Global flash toasts --}}
@if(session('error') || session('success') || session('info'))
<div id="flash-toast" style="position:fixed;top:68px;right:20px;z-index:999;max-width:400px;width:calc(100% - 40px);display:flex;flex-direction:column;gap:8px;">
  @if(session('error'))
  <div class="flash-msg flash-error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
    <span>{{ session('error') }}</span>
  </div>
  @endif
  @if(session('success'))
  <div class="flash-msg flash-success">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
    <span>{{ session('success') }}</span>
  </div>
  @endif
  @if(session('info'))
  <div class="flash-msg flash-info">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
    <span>{{ session('info') }}</span>
  </div>
  @endif
</div>
<style>
.flash-msg{display:flex;align-items:flex-start;gap:10px;padding:14px 16px;border-radius:12px;font-size:14px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,.14);animation:slideIn .25s ease;}
.flash-error{background:var(--danger-soft);color:var(--danger);border:1px solid var(--danger);}
.flash-success{background:var(--success-soft);color:var(--success);border:1px solid var(--success);}
.flash-info{background:var(--brand-primary-soft);color:var(--brand-primary);border:1px solid var(--brand-primary);}
.flash-msg svg{flex:none;margin-top:1px;}
@keyframes slideIn{from{opacity:0;transform:translateX(20px);}to{opacity:1;transform:translateX(0);}}
</style>
<script>
'use strict';
setTimeout(()=>{const t=document.getElementById('flash-toast');if(t)t.style.transition='opacity .4s',t.style.opacity='0',setTimeout(()=>t.remove(),400);},4000);
</script>
@endif

<main>@yield('content')</main>

@include('partials.footer')

@livewireScripts
<script>
'use strict';
document.addEventListener('click', function(e) {
  const menu = document.getElementById('userMenu');
  const pop = document.getElementById('userPop');
  if (menu && pop && !menu.contains(e.target)) pop.classList.remove('show');
});
</script>
@stack('scripts')
@if($platformSettings->google_analytics_id)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $platformSettings->google_analytics_id }}"></script>
<script>
'use strict';
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('js',new Date());gtag('config','{{ $platformSettings->google_analytics_id }}');
</script>
@endif
</body>
</html>
