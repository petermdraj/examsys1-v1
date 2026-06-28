<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request)
    {
        $locale = $request->input('locale');

        // Merge built-in config locales with admin-added extra locales
        $configLocales = array_keys(config('app.available_locales', ['en' => 'English']));
        $extraLocales  = array_keys(
            rescue(fn () => app(\App\Settings\PlatformSettings::class)->extra_locales, [], false) ?? []
        );
        $available = array_unique(array_merge($configLocales, $extraLocales));

        if (! in_array($locale, $available)) {
            return back();
        }

        session(['locale' => $locale]);

        if (auth()->check()) {
            auth()->user()->update(['preferred_locale' => $locale]);
        }

        return back();
    }
}
