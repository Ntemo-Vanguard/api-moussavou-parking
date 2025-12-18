<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Carte;

class TransactionController extends Controller
{
    public function index()
    {
        return response()->json(Transaction::with(['carte','utilisateur'])->get());
    }

    public function show($id)
    {
        return response()->json(Transaction::with(['carte','utilisateur'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $transaction = Transaction::create($request->all());

        // Si c'est une recharge → mettre à jour le solde
        if ($transaction->type === 'recharge' && $transaction->statut === 'valide') {
            $carte = Carte::findOrFail($transaction->carte_id);
            $carte->solde += $transaction->montant;
            $carte->save();
        }

        return response()->json($transaction, 201);
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->all());
        return response()->json($transaction);
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();
        return response()->json(null, 204);
    }
}