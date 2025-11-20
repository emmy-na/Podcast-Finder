<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Episode;
use App\Models\Podcast;
use App\Http\Requests\StoreEpisodeRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

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

        // Debug: Log request information
        Log::info('Episode store request:', [
            'has_audio_file' => $request->hasFile('audio_file'),
            'all_files' => $request->allFiles(),
            'audio_file_input' => $request->file('audio_file')
        ]);

        // Upload audio file to Cloudinary
        if ($request->hasFile('audio_file')) {
            try {
                // Get file size to determine if it's too large
                $fileSize = $request->file('audio_file')->getSize();
                $maxFileSize = 50 * 1024 * 1024; // 50MB limit
                
                if ($fileSize > $maxFileSize) {
                    Log::warning('File too large for upload:', ['size' => $fileSize]);
                    return response()->json([
                        'message' => 'Le fichier audio est trop volumineux. Taille maximale: 50MB',
                        'file_size' => $fileSize,
                        'max_size' => $maxFileSize
                    ], 400);
                }
                
                // Upload the file to Cloudinary with timeout configuration
                $uploadedFileUrl = Cloudinary::upload($request->file('audio_file')->getRealPath(), [
                    'folder' => 'podcast_episodes',
                    'timeout' => 120, // 2 minutes timeout
                    'resource_type' => 'video' // Cloudinary treats audio as video
                ])->getSecurePath();

                // Add the Cloudinary URL to validated data
                $validated['audio_file'] = $uploadedFileUrl;
                
                Log::info('Cloudinary upload successful:', ['url' => $uploadedFileUrl]);
            } catch (\Exception $e) {
                Log::error('Cloudinary upload failed:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Return a more user-friendly error message
                return response()->json([
                    'message' => 'Erreur lors du téléchargement du fichier audio. Veuillez réessayer avec un fichier plus petit.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // Create the episode
        $episode = Episode::create($validated);

        // Return response with debugging information
        return response()->json([
            'message' => 'Épisode créé avec succès',
            'episode' => $episode,
            'audio_file_url' => $episode->audio_file ?? null,
            'has_audio_file' => !empty($episode->audio_file),
        ], 201);
    }

    // ✅ UPDATE
    public function update(StoreEpisodeRequest $request, $id)
    {
        $episode = Episode::findOrFail($id);
        $validated = $request->validated();
        
        // Debug: Log request information
        Log::info('Episode update request:', [
            'episode_id' => $id,
            'has_audio_file' => $request->hasFile('audio_file'),
            'all_data' => $request->all(),
            'all_files' => $request->allFiles(),
            'method' => $request->method(),
            'is_put' => $request->isMethod('put'),
            'is_post' => $request->isMethod('post')
        ]);
        
        // Handle audio file update if provided
        if ($request->hasFile('audio_file')) {
            try {
                // Get file size to determine if it's too large
                $fileSize = $request->file('audio_file')->getSize();
                $maxFileSize = 50 * 1024 * 1024; // 50MB limit
                
                if ($fileSize > $maxFileSize) {
                    Log::warning('File too large for upload:', ['size' => $fileSize]);
                    return response()->json([
                        'message' => 'Le fichier audio est trop volumineux. Taille maximale: 50MB',
                        'file_size' => $fileSize,
                        'max_size' => $maxFileSize
                    ], 400);
                }
                
                $uploadedFileUrl = Cloudinary::upload($request->file('audio_file')->getRealPath(), [
                    'folder' => 'podcast_episodes',
                    'timeout' => 180, // 3 minutes timeout
                    'resource_type' => 'video'
                ])->getSecurePath();
                
                $validated['audio_file'] = $uploadedFileUrl;
            } catch (\Exception $e) {
                Log::error('Cloudinary update failed:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'message' => 'Erreur lors du téléchargement du fichier audio. Veuillez réessayer avec un fichier plus petit.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
        
        $episode->update($validated);
        return response()->json(['message' => 'Épisode mis à jour', 'episode' => $episode]);
    }

    // ✅ DESTROY
    public function destroy($id)
    {
        $episode = Episode::findOrFail($id);
        $episode->delete();
        return response()->json(['message' => 'Épisode supprimé']);
    }

    // Search episodes
    public function search(Request $request)
    {
        $query = $request->input('q');
        $episodes = Episode::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();

        return response()->json($episodes);
    }
}