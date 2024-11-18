<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        // Supprimer le token d'accès actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'You have successfully logged out!']);
    }

    public function store(LoginRequest $request)
    {
        try {
            \Log::info('Login attempt', ['email' => $request->email]); // Debug log

            $credentials = $request->validated();

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('token-name')->plainTextToken;

                \Log::info('Login successful', ['user' => $user->id]); // Debug log

                return response()->json([
                    'message' => 'Connexion réussie',
                    'user' => $user,
                    'token' => $token,
                ]);
            }

            \Log::warning('Login failed: invalid credentials'); // Debug log

            return response()->json([
                'message' => 'Identifiants incorrects',
            ], 401);
        } catch (\Exception $e) {
            \Log::error('Login error', ['error' => $e->getMessage()]); // Debug log

            return response()->json([
                'message' => 'Erreur lors de la connexion: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = auth()->user(); // Récupérer l'utilisateur authentifié

            $validator = Validator::make($request->all(), [
                'sexe' => 'nullable|in:Homme,Femme',
                'prenom' => 'nullable|string',
                'nom' => 'nullable|string',
                'post' => 'nullable|string',
                'password' => 'nullable|confirmed', // Confirmation du mot de passe
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
            }

            // Mise à jour des informations de l'utilisateur
            if ($request->filled('sexe')) $user->sexe = $request->sexe;
            if ($request->filled('prenom')) $user->prenom = $request->prenom;
            if ($request->filled('nom')) $user->nom = $request->nom;
            if ($request->filled('post')) $user->post = $request->post;

            // Gestion de la mise à jour du mot de passe
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // Gestion de l'upload de la nouvelle photo
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo si elle existe
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }
                // Enregistrer la nouvelle photo
                $photoName = time() . '.' . $request->photo->extension();
                $photoPath = $request->file('photo')->storeAs('photosUsers', $photoName, 'public');
                $user->photo = $photoPath;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Profil mis à jour avec succès.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
