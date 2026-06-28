<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationOtp;
use App\Models\User;
use App\Settings\PlatformSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function show()
    {
        $userId = session('otp_user_id');

        if (! $userId && (! auth()->check() || auth()->user()->hasVerifiedEmail())) {
            return redirect('/');
        }

        return view('auth.verify-otp');
    }

    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $userId = session('otp_user_id') ?? auth()->id();
        $user   = User::find($userId);

        if (! $user) {
            return redirect()->route('register')
                ->withErrors(['otp' => 'Session expired. Please register again.']);
        }

        if ($user->email_otp !== $request->otp) {
            return back()->withErrors(['otp' => 'The code you entered is incorrect. Please try again.']);
        }

        if (! $user->email_otp_expires_at || now()->isAfter($user->email_otp_expires_at)) {
            return back()->withErrors(['otp' => 'This code has expired. Please request a new one.']);
        }

        $user->update([
            'email_verified_at'    => now(),
            'email_otp'            => null,
            'email_otp_expires_at' => null,
        ]);

        session()->forget(['otp_user_id', 'otp_email']);
        auth()->login($user);

        return $user->role === 'creator'
            ? redirect('/creator')->with('status', 'Email verified! Welcome to Quizora.')
            : redirect(route('my.dashboard'))->with('status', 'Email verified! Welcome to Quizora.');
    }

    public function resend(Request $request)
    {
        $userId = session('otp_user_id') ?? auth()->id();
        $user   = User::find($userId);

        if (! $user || $user->hasVerifiedEmail()) {
            return redirect('/');
        }

        $settings = app(PlatformSettings::class);
        $otp      = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'email_otp'            => $otp,
            'email_otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(
            new EmailVerificationOtp($user, $otp, $settings->app_name)
        );

        session(['otp_email' => $user->email]);

        return back()->with('status', 'otp-resent');
    }
}
