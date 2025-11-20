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

// Debug endpoint to see request data
Route::middleware('auth:sanctum')->post('/debug-request', function (Request $request) {
    return response()->json([
        'all' => $request->all(),
        'only_name_email' => $request->only(['name', 'email']),
        'has_name' => $request->has('name'),
        'has_email' => $request->has('email'),
        'name_value' => $request->get('name'),
        'email_value' => $request->get('email'),
        'content_type' => $request->header('Content-Type')
    ]);
});

// Another debug endpoint for PUT requests
Route::middleware('auth:sanctum')->put('/debug-request/{id}', function (Request $request, $id) {
    return response()->json([
        'id' => $id,
        'all' => $request->all(),
        'only_name_email' => $request->only(['name', 'email']),
        'has_name' => $request->has('name'),
        'has_email' => $request->has('email'),
        'name_value' => $request->get('name'),
        'email_value' => $request->get('email'),
        'content_type' => $request->header('Content-Type')
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    
    // ✅ Update user profile (user and admin)
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can_update_user');
    
    // ✅ Delete user (user and admin)
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('can_delete_user');
    
    // ✅ Users (admin)
    Route::middleware('role:Administrateur')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
    });
    
    // ✅ Podcasts
    Route::get('/podcasts', [PodcastController::class, 'index']);
    Route::get('/podcasts/{id}', [PodcastController::class, 'show']);
    Route::post('/podcasts', [PodcastController::class, 'store'])->middleware('role:Animateur,Administrateur');
    Route::match(['put', 'post'], '/podcasts/{podcast}', [PodcastController::class, 'update'])->middleware('can_manage_podcast');
    Route::delete('/podcasts/{podcast}', [PodcastController::class, 'destroy'])->middleware('can_manage_podcast');

    // ✅ Episodes
    Route::get('/podcasts/{podcast_id}/episodes', [EpisodeController::class, 'index']);
    Route::get('/episodes/{id}', [EpisodeController::class, 'show']);
    Route::post('/podcasts/{podcast_id}/episodes', [EpisodeController::class, 'store'])->middleware('role:Animateur,Administrateur');
    Route::match(['put', 'post'], '/episodes/{episode}', [EpisodeController::class, 'update'])->middleware('can_manage_episode');
    Route::delete('/episodes/{episode}', [EpisodeController::class, 'destroy'])->middleware('can_manage_episode');
});

Route::get('/search/podcasts', [PodcastController::class, 'search']);
Route::get('/search/episodes', [EpisodeController::class, 'search']);