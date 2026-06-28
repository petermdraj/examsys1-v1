<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Settings\PlatformSettings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        } catch (\Exception) {
            return redirect()->route('login')->with('error', 'Google authentication failed. Please try again.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            if (! $this->settings->allow_registration) {
                return redirect()->route('login')->with('error', 'New registrations are currently closed.');
            }

            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'password'          => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
                'avatar'            => $googleUser->getAvatar(),
            ]);

            $user->assignRole('customer');
        }

        auth()->login($user, true);

        return redirect()->intended('/');
    }
}
