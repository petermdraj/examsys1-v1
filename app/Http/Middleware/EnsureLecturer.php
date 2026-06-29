<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLecturer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['lecturer', 'super_admin'], true)) {
            abort(403, __('common.lecturers_only'));
        }

        return $next($request);
    }
}
