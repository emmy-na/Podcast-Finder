<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanUpdateUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->route('user');
        $authUser = $request->user();
        
        // Allow user to update their own profile or admin to update any profile
        if ($authUser->id === $user->id || $authUser->isAdmin()) {
            return $next($request);
        }
        
        return response()->json(['error' => 'Accès refusé'], 403);
    }
}