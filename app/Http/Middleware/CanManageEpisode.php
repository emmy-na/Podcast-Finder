<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Episode;

class CanManageEpisode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the episode ID from the route
        $episodeId = $request->route('episode');
        
        // Resolve the episode model
        $episode = Episode::findOrFail($episodeId);
        
        $authUser = $request->user();
        
        // Allow episode creator or admin to manage the episode
        if ($authUser->id === $episode->podcast->user_id || $authUser->isAdmin()) {
            return $next($request);
        }
        
        return response()->json(['error' => 'Accès refusé'], 403);
    }
}