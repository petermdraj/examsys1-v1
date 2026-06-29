<?php

namespace App\Http\Controllers\Auth;

use App\Services\Auth\LoginHistoryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! auth()->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => __('auth.failed')], 'login')
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = auth()->user();

        app(LoginHistoryService::class)->record($user, $request);

        if ($user->role === 'super_admin') {
            return redirect()->intended('/admin');
        }

        if ($user->role === 'lecturer') {
            return redirect()->intended('/lecturer');
        }

        return redirect()->intended(route('my.dashboard'));
    }

    public function destroy(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
