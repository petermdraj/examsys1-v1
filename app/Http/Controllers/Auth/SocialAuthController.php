<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Settings\PlatformSettings;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(private PlatformSettings $settings) {}

    public function redirectToGoogle()
    {
        if (! $this->settings->allow_social_login) {
            return redirect()->route('login')->with('error', 'Social login is not enabled.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        if (! $this->settings->allow_social_login) {
            return redirect()->route('login')->with('error', 'Social login is not enabled.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::warning('Google authentication failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')->with('error', 'Google authentication failed. Please try again.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect()->route('login')->with(
                'error',
                'No account found for this email. Students are added by an administrator; lecturers must register with email and password.'
            );
        }

        auth()->login($user, true);

        return redirect()->intended('/');
    }
}
