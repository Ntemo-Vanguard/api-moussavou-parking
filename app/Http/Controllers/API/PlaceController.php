<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;

class PlaceController extends Controller
{
    public function index()
    {
        return response()->json(Place::with('parking')->get());
    }

    public function show($id)
    {
        return response()->json(Place::with('parking')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $place = Place::create($request->all());
        return response()->json($place, 201);
    }

    public function update(Request $request, $id)
    {
        $place = Place::findOrFail($id);
        $place->update($request->all());
        return response()->json($place);
    }

    public function destroy($id)
    {
        $place = Place::findOrFail($id);
        $place->delete();
        return response()->json(null, 204);
    }
}