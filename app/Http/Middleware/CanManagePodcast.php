<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Podcast;

class CanManagePodcast
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the podcast ID from the route
        $podcastId = $request->route('podcast');
        
        // Resolve the podcast model
        $podcast = Podcast::findOrFail($podcastId);
        
        $authUser = $request->user();
        
        // Allow podcast creator or admin to manage the podcast
        if ($authUser->id === $podcast->user_id || $authUser->isAdmin()) {
            return $next($request);
        }
        
        return response()->json(['error' => 'Accès refusé'], 403);
    }
}