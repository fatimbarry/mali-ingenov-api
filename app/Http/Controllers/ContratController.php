<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function index()
    {
        $contrats = Contrat::with(['projet.client'])->get();
        return response()->json($contrats);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_contrat' => 'required|in:Service,Maintenance,Consultation,Développement',
            'statut' => 'required|in:Actif,En attente,Terminé,Annulé',
            'montant' => 'required|numeric',
            'date' => 'required|date',
            'projet_id' => 'required|exists:projets,id',
        ]);

        $contrat = Contrat::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contrat créé avec succès.',
            'data' => $contrat,
        ]);
    }

    public function show($id)
    {
        $contrat = Contrat::with('projet')->find($id);

        if (!$contrat) {
            return response()->json(['error' => 'Contrat non trouvé.'], 404);
        }

        return response()->json($contrat);
    }

    public function update(Request $request, $id)
    {
        $contrat = Contrat::find($id);

        if (!$contrat) {
            return response()->json(['error' => 'Contrat non trouvé.'], 404);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type_contrat' => 'sometimes|in:Service,Maintenance,Consultation,Développement',
            'statut' => 'sometimes|in:Actif,En attente,Terminé,Annulé',
            'montant' => 'sometimes|numeric',
            'date' => 'sometimes|date',
            'projet_id' => 'sometimes|exists:projets,id',
        ]);

        $contrat->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contrat mis à jour avec succès.',
            'data' => $contrat,
        ]);
    }

    public function destroy($id)
    {
        $contrat = Contrat::find($id);

        if (!$contrat) {
            return response()->json(['error' => 'Contrat non trouvé.'], 404);
        }

        $contrat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contrat supprimé avec succès.',
        ]);
    }





}
