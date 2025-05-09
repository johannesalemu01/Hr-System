<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if($user->hasRole('super-admin')){
            return $next($request);
        }
        
        if (method_exists($user, 'hasRole')) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Unauthorized action.');
    }
}