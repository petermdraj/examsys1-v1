<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectUnverifiedUsers
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && ! $user->hasVerifiedEmail()) {
            $settings = app(\App\Settings\PlatformSettings::class);

            if ($settings->require_email_verification) {
                $userId = $user->id;
                $email  = $user->email;

                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                session(['otp_user_id' => $userId, 'otp_email' => $email]);

                return redirect()->route('otp.verify');
            }
        }

        return $next($request);
    }
}
