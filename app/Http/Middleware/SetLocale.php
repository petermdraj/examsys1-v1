<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Merge config locales + admin-added extra locales
        $configLocales = array_keys(config('app.available_locales', ['en' => 'English']));
        $extraLocales  = array_keys(
            rescue(fn () => app(\App\Settings\PlatformSettings::class)->extra_locales, [], false) ?? []
        );
        $available = array_unique(array_merge($configLocales, $extraLocales));

        $locale = $this->resolveLocale($request, $available);

        App::setLocale($locale);
        Carbon::setLocale($locale);

        // Share RTL flag with all views
        $rtlLocales = rescue(fn () => app(\App\Settings\PlatformSettings::class)->rtl_locales, [], false) ?? [];
        view()->share('isRtl', in_array($locale, $rtlLocales));

        return $next($request);
    }

    private function resolveLocale(Request $request, array $available): string
    {
        // 1. Explicit session switch (user clicked language switcher)
        $sessionLocale = session('locale');
        if ($sessionLocale && in_array($sessionLocale, $available)) {
            return $sessionLocale;
        }

        // 2. Authenticated user's saved DB preference
        if (Auth::check()) {
            $userLocale = Auth::user()->preferred_locale ?? null;
            if ($userLocale && in_array($userLocale, $available)) {
                return $userLocale;
            }
        }

        // 3. App default
        return config('app.locale', 'en');
    }
}
