<?php

namespace App\Http\Controllers;

use App\Models\ProjetModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjetController extends Controller
{
    public function index()
    {
        $projets = ProjetModel::where('archived', false)->with('client')->get();
        return response()->json($projets);
    }

    /**
     * Store a newly created project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //$this->authorize('create', Projet::class);

        $validatedData = $request->validate([
            'statut' => ['required', 'in:en cours,terminé,en attente'],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date'],
            'delai' => ['required', 'date'],
            'client_id' => ['required', 'exists:clients,id'],
        ]);

        $projet = new ProjetModel($validatedData);

        try {
            $projet->save();
            return response()->json(['message' => 'Projet créé avec succès', 'projet' => $projet], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du projet :', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors de la création du projet'], 500);
        }
    }

    /**
     * Display the specified project.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function show($id)
    {
        $projet = ProjetModel::with(['client', 'taches'])->find($id);

        if (!$projet) {
            return response()->json(['message' => 'Projet non trouvé'], 404);
        }

        return response()->json($projet);
    }


    /**
     * Update the specified project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //$this->authorize('update', Projet::class);

        $validatedData = $request->validate([
            'statut' => ['nullable', 'in:en cours,terminé,en attente'],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date'],
            'delai' => ['required', 'date'],
            'client_id' => ['required', 'exists:clients,id'],
        ]);

        $projet = ProjetModel::find($id);

        try {
            $projet->update($validatedData);
            return response()->json(['message' => 'Projet mis à jour avec succès', 'projet' => $projet]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du projet :', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors de la mise à jour du projet'], 500);
        }
    }

    /**
     * Archive the specified project.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        //$this->authorize('delete', ProjetModel::class);

        $projet = ProjetModel::find($id);

        if (!$projet) {
            return response()->json(['message' => 'Projet introuvable'], 404);
        }

        try {
            $projet->archived = true;
            $projet->save();

            return response()->json(['message' => 'Projet archivé avec succès']);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'archivage du projet :", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors de l\'archivage du projet'], 500);
        }
    }

}
