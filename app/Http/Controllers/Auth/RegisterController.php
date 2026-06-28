<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationOtp;
use App\Models\Plan;
use App\Models\Subscription;
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

        return view('auth.register');
    }

    public function store(Request $request)
    {
        if (! $this->settings->allow_registration) {
            return back()->withErrors(['email' => 'New registrations are currently closed.']);
        }

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role'     => 'in:customer,creator',
        ]);

        $role = $data['role'] ?? 'customer';

        if ($role === 'creator' && ! $this->settings->creator_registration_open) {
            return back()
                ->withErrors(['role' => 'Creator registration is currently closed. Please register as a customer.'])
                ->withInput();
        }

        $freePlan = Plan::where('slug', 'free')->where('is_active', true)->first()
            ?? Plan::where('is_active', true)->orderBy('price_monthly')->first();

        $emailVerifiedAt = $this->settings->require_email_verification ? null : now();

        $user = User::create([
            'name'                      => $data['name'],
            'email'                     => $data['email'],
            'password'                  => bcrypt($data['password']),
            'role'                      => $role,
            'ai_credits_free_remaining' => $freePlan?->ai_free_generations ?? 10,
            'email_verified_at'         => $emailVerifiedAt,
            'is_active'                 => true,
        ]);

        if ($user->role === 'creator' && $freePlan) {
            Subscription::create([
                'user_id'              => $user->id,
                'plan_id'              => $freePlan->id,
                'status'               => 'active',
                'billing_cycle'        => 'monthly',
                'current_period_start' => now(),
                'current_period_end'   => now()->addYear(),
                'gateway'              => 'manual',
            ]);
        }

        if ($this->settings->require_email_verification) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->update([
                'email_otp'            => $otp,
                'email_otp_expires_at' => now()->addMinutes(10),
            ]);

            Mail::to($user->email)->send(
                new EmailVerificationOtp($user, $otp, $this->settings->app_name)
            );

            session(['otp_user_id' => $user->id, 'otp_email' => $user->email]);

            return redirect()->route('otp.verify');
        }

        auth()->login($user);

        return $user->role === 'creator'
            ? redirect('/creator')
            : redirect(route('my.dashboard'));
    }
}
