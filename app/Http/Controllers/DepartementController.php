<?php

namespace App\Http\Controllers;

use App\Models\DepartmentModel;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    // Liste tous les départements
    public function index()
    {
        $departements = DepartmentModel::all();
        return response()->json($departements, 200);
    }


    // Crée un nouveau département
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        $departement = DepartmentModel::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Département créé avec succès.',
            'data' => $departement,
        ]);
    }

    // Affiche un département par ID
    public function show($id)
    {
        $departement = DepartmentModel::find($id);

        if (!$departement) {
            return response()->json([
                'status' => 'error',
                'message' => 'Département introuvable.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $departement,
        ]);
    }

    // Met à jour un département
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        $departement = DepartmentModel::find($id);

        if (!$departement) {
            return response()->json([
                'status' => 'error',
                'message' => 'Département introuvable.',
            ], 404);
        }

        $departement->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Département mis à jour avec succès.',
            'data' => $departement,
        ]);
    }

    // Supprime un département
    public function destroy($id)
    {
        $departement = DepartmentModel::find($id);

        if (!$departement) {
            return response()->json([
                'status' => 'error',
                'message' => 'Département introuvable.',
            ], 404);
        }

        $departement->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Département supprimé avec succès.',
        ]);
    }
}
