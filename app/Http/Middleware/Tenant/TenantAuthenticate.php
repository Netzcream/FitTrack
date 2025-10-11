<?php
namespace App\Http\Middleware\Tenant;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TenantAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('tenant.login');
        }

        return $next($request);
    }
}
