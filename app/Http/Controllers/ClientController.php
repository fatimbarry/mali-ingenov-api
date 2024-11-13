<?php

namespace App\Http\Controllers;

use App\Models\ClientModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Afficher tous les clients.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $clients = ClientModel::all();
        return response()->json($clients);
    }

    /**
     * Enregistrer un nouveau client.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $client = ClientModel::create($request->all());

        return response()->json(['success' => true, 'data' => $client], 201);
    }

    /**
     * Afficher un client spécifique.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $client = ClientModel::with('projets')->find($id);
            return response()->json(['success' => true, 'data' => $client]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Client non trouvé'], 404);
        }
    }

    /**
     * Mettre à jour un client spécifique.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'prenom' =>'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $id,
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $client = ClientModel::findOrFail($id);
            $client->update($request->all());
            return response()->json(['success' => true, 'data' => $client]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Client non trouvé'], 404);
        }
    }

    /**
     * Supprimer un client spécifique.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $client = ClientModel::findOrFail($id);
            $client->delete();
            return response()->json(['success' => true, 'message' => 'Client supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Client non trouvé'], 404);
        }
    }
}
