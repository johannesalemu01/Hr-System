<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::user() || !Auth::user()->hasRole('admin')) {
            // Redirect non-admin users to a different page
            return redirect('/employees'); // or any other appropriate route
        }

        return $next($request);
    }
} 