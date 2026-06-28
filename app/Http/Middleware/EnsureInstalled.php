<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        // Never intercept Livewire AJAX requests — they run in the context of
        // whatever page triggered them and must always pass through.
        if ($request->hasHeader('X-Livewire')) {
            return $next($request);
        }

        $installed = file_exists(storage_path('installed'));

        // On an install route — redirect away if already installed
        if ($request->is('install') || $request->is('install/*')) {
            if ($installed) {
                return redirect('/');
            }
            return $next($request);
        }

        // On any other route — redirect to installer if not installed
        if (!$installed) {
            return redirect()->route('installer');
        }

        return $next($request);
    }
}
