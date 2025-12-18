<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gestionnaire;

class GestionnaireController extends Controller
{
    public function index()
    {
        return response()->json(Gestionnaire::with('parkings')->get());
    }

    public function show($id)
    {
        return response()->json(Gestionnaire::with('parkings')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $gestionnaire = Gestionnaire::create($request->all());
        return response()->json($gestionnaire, 201);
    }

    public function update(Request $request, $id)
    {
        $gestionnaire = Gestionnaire::findOrFail($id);
        $gestionnaire->update($request->all());
        return response()->json($gestionnaire);
    }

    public function destroy($id)
    {
        $gestionnaire = Gestionnaire::findOrFail($id);
        $gestionnaire->delete();
        return response()->json(null, 204);
    }
}