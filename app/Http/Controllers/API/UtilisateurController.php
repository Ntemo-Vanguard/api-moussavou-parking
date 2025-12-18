<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Utilisateur;

class UtilisateurController extends Controller
{
    public function index()
    {
        return response()->json(Utilisateur::all());
    }

    public function show($id)
    {
        return response()->json(Utilisateur::findOrFail($id));
    }

    public function store(Request $request)
    {
        $utilisateur = Utilisateur::create($request->all());
        return response()->json($utilisateur, 201);
    }

    public function update(Request $request, $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $incomingFields = $request->validate([
            'nom' => 'sometimes|string|max:200',
            'email' => 'sometimes|email|unique:personnes,email,' . $utilisateur->id,
            'telephone' => 'sometimes|string|max:150',
            'mot_de_passe' => 'sometimes|nullable|string|min:4',
            'role' => 'sometimes|in:admin,gestionnaire',
            'is_blocked' => 'sometimes|boolean',
        ]);

        // Si mot_de_passe non fourni → le retirer
        if (empty($incomingFields['mot_de_passe'])) {
            unset($incomingFields['mot_de_passe']);
        }

        $utilisateur->update($incomingFields);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès.',
            'data' => $utilisateur
        ]);
    }

    public function destroy($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $utilisateur->delete();
        return response()->json(null, 204);
    }
}