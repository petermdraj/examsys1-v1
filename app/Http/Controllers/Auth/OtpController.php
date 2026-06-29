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
            return redirect()->route('login')
                ->withErrors(['otp' => 'Session expired. Please log in again.']);
        }

        if (! $user->verifyEmailOtp($request->otp)) {
            return back()->withErrors(['otp' => 'The code you entered is incorrect or has expired. Please try again.']);
        }

        $user->markEmailVerified();

        session()->forget(['otp_user_id', 'otp_email']);
        auth()->login($user);

        return $user->role === 'lecturer'
            ? redirect('/lecturer')->with('status', 'Email verified! Welcome!')
            : redirect(route('my.dashboard'))->with('status', 'Email verified! Welcome!');
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

        $user->assignEmailOtp($otp);

        Mail::to($user->email)->send(
            new EmailVerificationOtp($user, $otp, $settings->app_name)
        );

        session(['otp_email' => $user->email]);

        return back()->with('status', 'otp-resent');
    }
}
