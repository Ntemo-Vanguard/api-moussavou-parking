<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parking;
use App\Models\Place;

class ParkingController extends Controller
{
    public function index()
    {
        return response()->json(Parking::with(['places', 'gestionnaire'])->get());
    }

    public function show($id)
    {
        return response()->json(Parking::with(['places', 'gestionnaire'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'localisation' => 'required|string|max:255',
            'capacite' => 'required|integer|min:1',
            'gestionnaire_id' => 'nullable|exists:personnes,id',
        ]);

        $parking = Parking::create($data);

        // 🔁 Générer automatiquement les places P01..PN
        for ($i = 1; $i <= $parking->capacite; $i++) {
            Place::create([
                'parking_id' => $parking->id,
                'numero' => 'P' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'statut' => 'libre',
                'code_capteur' => null,
            ]);
        }

        return response()->json($parking->load('places'), 201);
    }

    public function update(Request $request, $id)
    {
        $parking = Parking::findOrFail($id);

        $data = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'localisation' => 'sometimes|required|string|max:255',
            'capacite' => 'sometimes|required|integer|min:1',
            'gestionnaire_id' => 'nullable|exists:personnes,id',
        ]);

        $capaciteChange = isset($data['capacite']) && $data['capacite'] != $parking->capacite;

        $parking->update($data);

        if ($capaciteChange) {

            // Supprimer toutes les anciennes places
            Place::where('parking_id', $parking->id)->delete();

            // 2️⃣ Créer les nouvelles
            for ($i = 1; $i <= $parking->capacite; $i++) {
                Place::create([
                    'parking_id'   => $parking->id,
                    'numero'       => 'P' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'statut'       => 'libre',
                    'code_capteur' => null,
                ]);
            }
        }

        return response()->json($parking->load('places'));
    }

    public function destroy($id)
    {
        $parking = Parking::findOrFail($id);
        $parking->delete();
        return response()->json(null, 204);
    }
}