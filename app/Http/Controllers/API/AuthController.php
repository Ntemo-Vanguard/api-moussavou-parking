<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;
use App\Models\Admin;
use App\Models\Carte;
use App\Models\Client;
use App\Models\Personne;
use App\Models\Gestionnaire;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $incomingFields = $request->validate([
            'nom' => 'required|string|max:200',
            'email' => 'required|email|unique:personnes,email',
            'telephone' => 'required|string|max:150',
            'mot_de_passe' => 'required|string|min:4'
        ]);

        // Vérifier s'il existe déjà un admin
        $adminExiste = Personne::where('role', 'admin')->exists();

        if (!$adminExiste) {
            // Création du tout premier administrateur
            $admin = Admin::create($incomingFields);

            return response()->json(['message' => 'Premier utilisateur créé en tant qu\'administrateur.'], 201);
        }

        // Sinon création d'un client
        $client = Client::create($incomingFields);

        // Créer automatiquement la carte RFID du client
        Carte::create([
            'utilisateur_id' => $client->id,
            'solde'          => 0,
            'statut'         => 'active'
            // code RFID généré automatiquement dans le modèle Carte.php
        ]);

        return response()->json(['message' => 'Client créé avec succès.'], 201);
        Log::error($e->getMessage());
    }

    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'mot_de_passe' => $request->mot_de_passe
        ];

        $personne = Personne::where('email', $request->email)->first();

        if (! $personne || !Hash::check($request->mot_de_passe, $personne->mot_de_passe)) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        // Vérifier si bloqué
        if ($personne->is_blocked) {
            return response()->json(['message' => 'Votre compte est bloqué.'], 403);
        }

        // Générer le token
        $token = JWTAuth::fromUser($personne);

        return response()->json([
            'token' => $token,
            'user'  => $personne
        ]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Déconnexion réussie']);
    }
}