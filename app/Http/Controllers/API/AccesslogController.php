<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accesslog;

class AccesslogController extends Controller
{
    public function index()
    {
        return response()->json(Accesslog::with(['carte.utilisateur','parking'])->get());
    }

    public function show($id)
    {
        return response()->json(Accesslog::with(['carte.utilisateur','parking'])->findOrFail($id));
    }
}