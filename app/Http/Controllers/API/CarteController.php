<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carte;
use App\Models\Transaction;
use App\Mail\CarteRechargeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CarteController extends Controller
{
    public function index()
    {
        return response()->json(Carte::with(['utilisateur','transactions'])->get());
    }

    public function show($id)
    {
        return response()->json(Carte::with(['utilisateur','transactions'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $carte = Carte::create($request->all());
        return response()->json($carte, 201);
    }

    public function update(Request $request, $id)
    {
        $carte = Carte::findOrFail($id);
        $carte->update($request->all());
        return response()->json($carte);
    }

    public function destroy($id)
    {
        $carte = Carte::findOrFail($id);
        $carte->delete();
        return response()->json(null, 204);
    }

    public function recharger(Request $request, $id)
    {
        $carte = Carte::with('utilisateur')->findOrFail($id);

        $data = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'moyen'   => 'nullable|string|max:50',
        ]);

        if ($carte->statut !== 'active') {
            return response()->json(['message' => 'Impossible de recharger une carte bloquée.'], 422);
        }

        DB::transaction(function () use (&$carte, $data) {

            // Mise à jour solde
            $carte->increment('solde', $data['montant']);

            Transaction::create([
                'carte_id'       => $carte->id,
                'utilisateur_id' => $carte->utilisateur_id,
                'montant'        => $data['montant'],
                'type'           => 'recharge',
                'moyen'          => $data['moyen'] ?? 'cash',
                'statut'         => 'valide',
            ]);
        });

        Mail::to($carte->utilisateur->email)->send(new CarteRechargeNotification(
            $carte->fresh('utilisateur'),
            $data['montant'],
            $data['moyen'] ?? 'cash'
        ));

        // On renvoie la carte rafraîchie
        return response()->json($carte->fresh('utilisateur'));
    }
}