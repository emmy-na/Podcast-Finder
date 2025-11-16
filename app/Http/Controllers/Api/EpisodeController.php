<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Episode;
use App\Models\Podcast;
use App\Http\Requests\StoreEpisodeRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class EpisodeController extends BaseApiController
{
    // ✅ LIST EPISODES BY PODCAST
    public function index($podcast_id)
    {
        return Episode::where('podcast_id', $podcast_id)->get();
    }

    // ✅ SHOW ONE
    public function show($id)
    {
        return Episode::with('podcast')->findOrFail($id);
    }

    // ✅ STORE
    public function store(StoreEpisodeRequest $request, $podcast_id)
    {
        $validated = $request->validated();
        
        // Add podcast_id to validated data
        $validated['podcast_id'] = $podcast_id;

        // Upload audio file to Cloudinary
        if ($request->hasFile('audio_file')) {
            $uploadedFile = Cloudinary::uploadFile(
                $request->file('audio_file')->getRealPath(),
                [
                    'folder' => 'podcast-episodes',
                    'resource_type' => 'video' // Cloudinary uses 'video' for audio files
                ]
            );
            $validated['audio_file'] = $uploadedFile->getSecurePath();
        }

        // Create episode with uploaded audio URL
        $episode = Episode::create($validated);

        return response()->json($episode, 201);
    }

    // ✅ UPDATE
    public function update(StoreEpisodeRequest $request, Episode $episode)
    {
        $this->authorizePodcast($request, $episode->podcast);

        $validated = $request->validated();

        // Upload new audio file if provided
        if ($request->hasFile('audio_file')) {
            $uploadedFile = Cloudinary::uploadFile(
                $request->file('audio_file')->getRealPath(),
                [
                    'folder' => 'podcast-episodes',
                    'resource_type' => 'video'
                ]
            );
            $validated['audio_file'] = $uploadedFile->getSecurePath();
        }

        // Update episode
        $episode->update($validated);
        
        return response()->json(['message' => 'Épisode mis à jour', 'episode' => $episode->fresh()]);
    }

    // ✅ DELETE
    public function destroy(Request $request, Episode $episode)
    {
        $this->authorizePodcast($request, $episode->podcast);
        $episode->delete();
        return response()->json(['message' => 'Épisode supprimé']);
    }

    //Recherche API
    public function search(Request $request)
    {
        $query = $request->input('q');
        $episodes = Episode::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();

        return response()->json($episodes);
    }
}