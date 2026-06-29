<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationOtp;
use App\Models\User;
use App\Settings\PlatformSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function __construct(private PlatformSettings $settings) {}

    public function create()
    {
        if (! $this->settings->allow_registration) {
            return redirect()->route('login')->with('error', 'New registrations are currently closed.');
        }

        if (! $this->settings->lecturer_registration_open) {
            return redirect()->route('login')->with('error', 'Public registration is closed. Students are added by an administrator.');
        }

        return view('auth.register');
    }

    public function store(Request $request)
    {
        if (! $this->settings->allow_registration) {
            return back()->withErrors(['email' => 'New registrations are currently closed.']);
        }

        if (! $this->settings->lecturer_registration_open) {
            return back()->withErrors(['email' => 'Public registration is closed. Students are added by an administrator.']);
        }

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:lecturer',
        ]);

        $emailVerifiedAt = $this->settings->require_email_verification ? null : now();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->forceFill([
            'role'                      => 'lecturer',
            'ai_credits_free_remaining' => (int) config('examsys.ai_free_generations_default', 10),
            'email_verified_at'         => $emailVerifiedAt,
            'is_active'                 => true,
        ])->save();

        $user->assignRole('lecturer');

        if ($this->settings->require_email_verification) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->assignEmailOtp($otp);

            Mail::to($user->email)->send(
                new EmailVerificationOtp($user, $otp, $this->settings->app_name)
            );

            session(['otp_user_id' => $user->id, 'otp_email' => $user->email]);

            return redirect()->route('otp.verify');
        }

        auth()->login($user);

        return redirect('/lecturer');
    }
}
