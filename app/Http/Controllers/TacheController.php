<?php
namespace App\Http\Controllers;

use App\Models\TacheModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TacheController extends Controller
{
    // Méthode pour afficher toutes les tâches avec le projet associé
    public function index()
    {
        $taches = TacheModel::with('projet')->get();
        return response()->json($taches);
    }

    // Méthode pour créer une nouvelle tâche
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
        }
        // Vérification du rôle de l'utilisateur
        if (Auth::user()->role !== 'Chef_de_projet') {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à créer des tâches.'], 403);
        }

        $validatedData = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'temps_previs' => 'nullable|date_format:H:i:s',
            'due_date' => 'nullable|date',
            'status' => 'in:en cours,terminé,validé,A faire',
            'projet_id' => 'required|exists:projets,id',
        ]);

        $tache = TacheModel::create($validatedData);
        return response()->json($tache, 201);
    }

    // Méthode pour mettre à jour une tâche existante
    public function update(Request $request, $id)
    {
        // Vérification du rôle de l'utilisateur
        if (Auth::user()->role !== 'Chef_de_projet') {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à modifier des tâches.'], 403);
        }

        $tache = TacheModel::findOrFail($id);

        $validatedData = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'temps_previs' => 'nullable|date_format:H:i:s',
            'status' => 'in:en cours,terminé,validé','À faire',
            'projet_id' => 'required|exists:projets,id',
        ]);

        $tache->update($validatedData);
        return response()->json($tache);
    }

    // Méthode pour supprimer une tâche
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
        }
        // Vérification du rôle de l'utilisateur
        if (Auth::user()->role !== 'Chef_de_projet') {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à supprimer des tâches.'], 403);
        }

        $tache = TacheModel::findOrFail($id);
        $tache->delete();
        return response()->json(null, 204);
    }

    public function assignTask(Request $request, $taskId)
    {
        $validatedData = $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);

        $task = TacheModel::findOrFail($taskId);

        $task->update([
            'assigned_to' => $validatedData['assigned_to'],
            'assigned_by' => auth()->user()->id,
            'status' => 'todo' // Réinitialiser le statut lors de la nouvelle assignation
        ]);

        // Déclencher l'événement de notification
        User::findOrFail($validatedData['assigned_to']);
        //event(new TaskAssignedEvent($task, $assignedUser));

        return response()->json($task);
    }
    public function getTaskDetails($taskId)
    {
        // Récupérer la tâche spécifique avec ses relations
        $task = TacheModel::with(['assignedTo', 'assignedBy'])->find($taskId);

        if (!$task) {
            // Si la tâche n'existe pas, retourner une réponse avec une erreur
            return response()->json(['message' => 'Tâche non trouvée'], 404);
        }

        return response()->json($task);
    }


}

