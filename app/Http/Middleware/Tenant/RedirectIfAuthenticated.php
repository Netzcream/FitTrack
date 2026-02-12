<?php

namespace App\Http\Middleware\Tenant;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $target = route('tenant.dashboard');

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('Alumno')) {
            $target = route('tenant.student.dashboard');
        }

        return redirect()->to($target);
    }
}
