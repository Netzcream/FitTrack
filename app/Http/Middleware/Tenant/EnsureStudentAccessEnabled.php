<?php

namespace App\Http\Middleware\Tenant;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentAccessEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $student = $request->user()?->student;

        if (!$student || !$student->is_user_enabled) {
            Auth::logout();
            return redirect()->route('tenant.login')->withErrors([
                'email' => __('Tu acceso como alumno no está habilitado. Contactá a tu entrenador.'),
            ]);
        }

        return $next($request);
    }
}
