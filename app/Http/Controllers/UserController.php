<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\NewUserCredentials;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
//   public function index(Request $request)
//{
//    try {
//        // Récupérer les paramètres de la requête
//        $perPage = $request->input('per_page', 8);
//        $search = $request->input('search', '');
//
//        // Construire la requête de base
//        $query = User::select('id', 'nom', 'prenom', 'photo', 'role', 'status');
//
//        // Ajouter la recherche si un terme est fourni
//        if ($search) {
//            $query->where(function($q) use ($search) {
//                $q->where('nom', 'like', "%{$search}%")
//                  ->orWhere('prenom', 'like', "%{$search}%")
//                  ->orWhere('role', 'like', "%{$search}%");
//            });
//        }
//
//        // Exécuter la pagination
//        $users = $query->paginate($perPage);
//
//        // Transformer les URLs des photos
//        $users->getCollection()->transform(function ($user) {
//            if ($user->photo) {
//                $user->photo = url('storage/' . $user->photo);
//            }
//
//            // Concaténer nom et prénom pour l'affichage
//            $user->full_name = $user->nom . ' ' . $user->prenom;
//
//            return $user;
//        });
//
//        // Retourner la réponse avec toutes les informations de pagination
//        return response()->json([
//            'success' => true,
//            'data' => $users->items(),
//            'pagination' => [
//                'total' => $users->total(),
//                'per_page' => $users->perPage(),
//                'current_page' => $users->currentPage(),
//                'last_page' => $users->lastPage(),
//                'from' => $users->firstItem(),
//                'to' => $users->lastItem()
//            ]
//        ]);
//
//    } catch (\Exception $e) {
//        return response()->json([
//            'success' => false,
//            'message' => 'Une erreur est survenue lors de la récupération des utilisateurs',
//            'error' => $e->getMessage()
//        ], 500);
//    }
//}

    public function index()
    {
        try {
            $users = User::select('id', 'nom', 'prenom', 'photo', 'role', 'status', 'email', 'post', 'date_Emb', 'department_id')
                ->with('department:id,nom') // Chargez la relation department
                ->paginate(8);

            $users->getCollection()->transform(function ($user) {
                if ($user->photo) {
                    $user->photo = url('storage/' . $user->photo);
                }

                if ($user->department) {
                    $user->department = $user->department->nom; // Ajout du nom du département
                }
                $user->date_Emb = $user->date_Emb ? date('Y-m-d', strtotime($user->date_Emb)) : null;
                return $user;
            });
            return response()->json([
                'success' => true,
                'data' => $users

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sexe' => 'required|in:Homme,Femme',
                'prenom' => 'required|string',
                'nom' => 'required|string',
                'date_Emb' => 'required|date',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|in:Admin,Comptable,Employé,Chef_de_projet',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'post' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
            }

            // Générer un mot de passe aléatoire
            $defaultPassword = Str::random(10);

            $data = $request->only([
                'sexe', 'prenom', 'nom', 'date_Emb', 'email', 'post', 'role', 'department_id'
            ]);
            $data['password'] = Hash::make($defaultPassword);

            // Gestion de l'upload de la photo
            if ($request->hasFile('photo')) {
                $photoName = time() . '.' . $request->photo->extension();
                $photoPath = $request->file('photo')->storeAs('photosUsers', $photoName, 'public');
                $data['photo'] = $photoPath;
            }

            $user = User::create($data);

            // Envoyer l'email avec les identifiants
            Mail::to($user->email)->send(new NewUserCredentials($user, $defaultPassword));

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Utilisateur créé avec succès. Un email contenant les identifiants a été envoyé.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        try {
            $validator = Validator::make($request->all(), [
                'sexe' => 'required|in:Homme,Femme',
                'prenom' => 'required|string',
                'nom' => 'required|string',
                'date_Emb' => 'required|date',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => ['nullable', 'confirmed', Password::defaults()],
                'role' => 'required',
                'post' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
            }

            $user->fill($request->only([
                'sexe', 'prenom', 'nom', 'date_Emb', 'email', 'post', 'role'
            ]));

            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }

            // Gestion de la mise à jour de la photo
            if ($request->hasFile('photo')) {
                $photoName = time() . '.' . $request->photo->extension();
                $photoPath = $request->file('photo')->storeAs('photosUsers', $photoName, 'public');
                $user->photo = $photoPath;
            }

            $user->save();

            return response()->json(['success' => true, 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $user->delete();

            return response()->json(['success' => true, 'message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting user: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to delete user'], 500);
        }
    }

    public function getUser(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'prenom' => $user->prenom,
            'nom' => $user->nom,
            'photo' => $user->photo,
            'role' => $user->role,
            'email' =>$user->email,
            'post' =>$user->post
        ]);
    }

//public function index(Request $request)
//{
//    $perPage = $request->input('per_page', 10);
//    $search = $request->input('search', '');
//
//    $query = User::query();
//
//    if ($search) {
//        $query->where('nom', 'like', "%{$search}%")
//              ->orWhere('email', 'like', "%{$search}%");
//
//    }
//
//    return $query->paginate($perPage);
//}
}
