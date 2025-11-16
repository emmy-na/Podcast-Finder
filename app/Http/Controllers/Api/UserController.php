<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class UserController extends Controller
{

public function register(Request $request)
{
    // ✅ Validate inputs
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
        'role' => 'in:Utilisateur,Animateur,Administrateur', // optional field
    ]);

    // ✅ Create the user
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => $validated['role'] ?? 'Utilisateur', // default if not provided
    ]);

    // ✅ Generate Sanctum token
    $token = $user->createToken('auth_token')->plainTextToken;

    // ✅ Return clean JSON response
    return response()->json([
        'message' => 'Utilisateur créé avec succès.',
        'token_type' => 'Bearer',
        'token' => $token,
        'user' => $user,
    ], 201);
}

    // // ✅ LOGIN
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|string|email',
    //         'password' => 'required|string',
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         throw ValidationException::withMessages([
    //             'email' => ['Les identifiants sont incorrects.'],
    //         ]);
    //     }

    //     // try it withn attempt off sanctum

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'message' => 'Connexion réussie',
    //         'token' => $token,
    //         'user' => $user
    //     ]);
    // }


    public function login(Request $request)
{
    // ✅ Validate input
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    // ✅ Try to log in the user
    if (!Auth::attempt($credentials)) {
        throw ValidationException::withMessages([
            'email' => ['Les identifiants sont incorrects.'],
        ]);
    }

    // ✅ Retrieve authenticated user
    $user = Auth::user();

    // ✅ Optionally, delete old tokens to force single session
    $user->tokens()->delete();

    // ✅ Create new token via Sanctum
    $token = $user->createToken('auth_token')->plainTextToken;

    // ✅ Return response
    return response()->json([
        'message' => 'Connexion réussie',
        'token_type' => 'Bearer',
        'token' => $token,
        'user' => $user,
    ], 200);
}

    // ✅ LOGOUT
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    // ✅ LIST USERS (admin)
    public function index()
    {
        return response()->json(User::all());
    }

    // ✅ UPDATE ROLE pour l'admin
    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:Utilisateur,Animateur,Administrateur']);
        $user->update(['role' => $request->role]);
        return response()->json(['message' => 'Rôle mis à jour', 'user' => $user]);
    }

    // ✅ DELETE USER for the admin and the user him self 
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}
