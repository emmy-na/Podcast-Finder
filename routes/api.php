<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PodcastController;
use App\Http\Controllers\Api\EpisodeController;

// ✅ AUTH
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Test endpoint to verify authentication
Route::middleware('auth:sanctum')->get('/test-auth', function (Request $request) {
    return response()->json([
        'message' => 'Authenticated!',
        'user' => $request->user()
    ]);
});

// Test endpoint to create podcast
Route::middleware('auth:sanctum')->post('/test-podcast', function (Request $request) {
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'user_id' => 'required|exists:users,id',
    ]);
    
    // Handle image upload like in the controller
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images', 'public');
        $validated['image'] = '/storage/' . $imagePath;
    }
    
    return response()->json(['validated' => $validated]);
});

// Test endpoint to create actual podcast
Route::middleware('auth:sanctum')->post('/test-create-podcast', function (Request $request) {
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'user_id' => 'required|exists:users,id',
    ]);
    
    // Handle image upload
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images', 'public');
        $validated['image'] = '/storage/' . $imagePath;
    }
    
    // Try to create podcast
    try {
        $podcast = \App\Models\Podcast::create($validated);
        return response()->json(['podcast' => $podcast], 201);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
    }
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    
    // ✅ Users (admin)
    Route::middleware('role:Administrateur')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
    
    // ✅ Podcasts
    Route::get('/podcasts', [PodcastController::class, 'index']);
    Route::get('/podcasts/{id}', [PodcastController::class, 'show']);
    Route::post('/podcasts', [PodcastController::class, 'store'])->middleware('role:Animateur,Administrateur');
    Route::put('/podcasts/{podcast}', [PodcastController::class, 'update'])->middleware('role:Animateur,Administrateur');
    Route::delete('/podcasts/{podcast}', [PodcastController::class, 'destroy'])->middleware('role:Animateur,Administrateur');

    // ✅ Episodes
    Route::get('/podcasts/{podcast_id}/episodes', [EpisodeController::class, 'index']);
    Route::get('/episodes/{id}', [EpisodeController::class, 'show']);
    Route::post('/podcasts/{podcast_id}/episodes', [EpisodeController::class, 'store'])->middleware('role:Animateur,Administrateur');
    Route::put('/episodes/{episode}', [EpisodeController::class, 'update'])->middleware('role:Animateur,Administrateur');
    Route::delete('/episodes/{episode}', [EpisodeController::class, 'destroy'])->middleware('role:Animateur,Administrateur');
});


Route::get('/search/podcasts', [PodcastController::class, 'search']);
Route::get('/search/episodes', [EpisodeController::class, 'search']);

