<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Carte;
use App\Models\Place;
use App\Models\Parking;
use App\Models\Accesslog;
use App\Models\Transaction;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function parkingsStatus()
    {
        return response()->json(Parking::with(['places', 'gestionnaire'])->get());
    }


    /**
     * 🔑 Pointage RFID (appelé par Python via USB)
     * UID lu → décision accès → retour commande servo
     */
    public function rfidAccess(Request $request)
    {
        $data = $request->validate([
            'uid'        => 'required|string',
            'parking_id' => 'required|exists:parkings,id',
        ]);

        // 1️⃣ Recherche carte
        $carte = Carte::where('code_rfid', $data['uid'])->first();

        // Carte inconnue
        if (!$carte) {
            return response()->json([
                'status'  => 'refuse',
                'message' => 'Carte invalide',
                'action'  => 'KEEP_LOCKED'
            ]);
        }

        $utilisateur = $carte->utilisateur;

        // 2️⃣ Utilisateur bloqué
        if ($utilisateur->is_blocked) {
            Accesslog::create([
                'carte_id'    => $carte->id,
                'parking_id' => $data['parking_id'],
                'statut'      => 'refuse',
                'raison'      => 'Utilisateur bloqué',
            ]);

            return response()->json([
                'status'  => 'refuse',
                'message' => 'Utilisateur bloqué',
                'action'  => 'KEEP_LOCKED'
            ]);
        }

        // 3️⃣ Vérification du solde
        if ($carte->solde < 100) {
            Accesslog::create([
                'carte_id'    => $carte->id,
                'parking_id' => $data['parking_id'],
                'statut'      => 'refuse',
                'raison'      => 'Solde insuffisant',
            ]);

            return response()->json([
                'status'  => 'refuse',
                'message' => 'Solde insuffisant',
                'action'  => 'KEEP_LOCKED'
            ]);
        }

        // 4️⃣ Accès autorisé (transaction atomique)
        DB::transaction(function () use ($carte, $data) {

            // Débit
            $carte->decrement('solde', 100);

            // Transaction parking
            Transaction::create([
                'carte_id'       => $carte->id,
                'utilisateur_id' => $carte->utilisateur_id,
                'montant'        => 100,
                'type'           => 'paiement_parking',
                'statut'         => 'valide',
            ]);

            // Journal d’accès
            Accesslog::create([
                'carte_id'    => $carte->id,
                'parking_id' => $data['parking_id'],
                'statut'      => 'accepte',
            ]);
        });

        return response()->json([
            'status'  => 'accepte',
            'message' => 'Accès autorisé',
            'action'  => 'OPEN_GATE'
        ]);
    }

    /**
     * 🚗 Mise à jour de l’état des places (capteurs → Python → API)
     */
    public function updatePlacesStatus(Request $request)
    {
        $data = $request->validate([
            'parking_id' => 'required|exists:parkings,id',
            'places'     => 'required|array|min:1',
            'places.*.code_capteur' => 'required|string',
            'places.*.statut'       => 'required|in:libre,occupee',
        ]);

        $inexistant = [];
        $updatedCount = 0;

        foreach ($data['places'] as $p) {
            $place = Place::where('parking_id', $data['parking_id'])
                ->where('code_capteur', $p['code_capteur'])
                ->first();

            if (!$place) {
                $inexistant[] = $p['code_capteur'];
                continue;
            }

            $place->update(['statut' => $p['statut']]);
            $updatedCount++;
        }

        // ❌ Si rien n'a été mis à jour
        if ($updatedCount === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucune place mise à jour',
                'inexistant' => $inexistant
            ], 422);
        }

        // ⚠️Si OK Partiel
        if (!empty($inexistant)) {
            return response()->json([
                'status' => 'partial',
                'message' => 'OK partiel',
                'updated' => $updatedCount,
                'inexistant' => $inexistant
            ], 207);
        }

        // ✅ Si OK
        return response()->json(['message' => 'État des places mis à jour']);
    }
}