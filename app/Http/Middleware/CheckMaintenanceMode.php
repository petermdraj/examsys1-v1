<?php

namespace App\Http\Middleware;

use App\Settings\PlatformSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function __construct(protected PlatformSettings $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Settings table may not exist or may not be seeded yet (installer).
        // Bail out safely so the installer can complete without interference.
        try {
            $maintenanceMode = $this->settings->maintenance_mode;
        } catch (\Throwable) {
            return $next($request);
        }

        if (! $maintenanceMode) {
            return $next($request);
        }

        // Allow admin and creator panels through
        if ($request->is('admin*') || $request->is('creator*')) {
            return $next($request);
        }

        // Allow authenticated admins through
        if ($request->user() && $request->user()->hasRole('super_admin')) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [
            'message' => $this->settings->maintenance_message,
        ], 503);
    }
}
