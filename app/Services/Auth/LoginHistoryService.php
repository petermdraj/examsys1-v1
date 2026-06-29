<?php

namespace App\Services\Auth;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;

class LoginHistoryService
{
    public function record(User $user, Request $request): LoginHistory
    {
        return LoginHistory::create([
            'user_id'      => $user->id,
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
            'logged_in_at' => now(),
        ]);
    }
}
