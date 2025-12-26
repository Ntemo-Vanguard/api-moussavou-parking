<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\CredentialsUtilisateurMail;
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
        $incomingFields = $request->validate([
            'nom' => 'required|string|max:200',
            'email' => 'required|email|unique:personnes,email',
            'telephone' => 'required|string|max:150',
            'role' => 'required|in:admin,gestionnaire',
            'mot_de_passe' => 'required|string|min:4',
        ]);

        // ⚠️ On garde le mot de passe en clair POUR L’EMAIL UNIQUEMENT
        $plainPassword = $incomingFields['mot_de_passe'];

        $utilisateur = Utilisateur::create($incomingFields);

        Mail::to($utilisateur->email)->send(new CredentialsUtilisateurMail($utilisateur, $plainPassword));

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