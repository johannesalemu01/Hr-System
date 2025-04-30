<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

             
        $user = Auth::user();
        
        
        if (method_exists($user, 'hasPermissionTo')) {
            foreach ($permissions as $permission) {
                if ($user->hasPermissionTo($permission)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Unauthorized action.');
    }
}