<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Podcast;
use App\Http\Requests\StorePodcastRequest;

class PodcastController extends BaseApiController
{
    // ✅ LIST
    public function index()
    {
        return Podcast::with('user', 'episodes')->get();
    }

    // ✅ SHOW
    public function show($id)
    {
        $podcast = Podcast::with('episodes', 'user')->findOrFail($id);
        return response()->json($podcast);
    }

    // ✅ STORE
    public function store(StorePodcastRequest $request)
    {
        $validated = $request->validated();
        
        // Automatically set the user_id to the authenticated user
        $validated['user_id'] = $request->user()->id;

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $validated['image'] = '/storage/' . $imagePath;
        }

        // Create podcast
        $podcast = Podcast::create($validated);
        return response()->json($podcast, 201);
    }

    // ✅ UPDATE
    public function update(StorePodcastRequest $request, Podcast $podcast)
    {
        $validated = $request->validated();
        
        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $validated['image'] = '/storage/' . $imagePath;
        }

        // Update podcast
        $podcast->update($validated);

        return response()->json(['message' => 'Podcast mis à jour', 'podcast' => $podcast->fresh()]);
    }

    // ✅ DELETE
    public function destroy(Podcast $podcast)
    {
        $podcast->delete();
        return response()->json(['message' => 'Podcast supprimé']);
    }

    //Recherche API
    public function search(Request $request)
    {
        $query = $request->input('q');
        $podcasts = Podcast::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();

        return response()->json($podcasts);
    }
}