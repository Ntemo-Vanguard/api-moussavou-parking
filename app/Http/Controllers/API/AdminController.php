<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;

class AdminController extends Controller
{
    public function index()
    {
        return response()->json(Admin::all());
    }

    public function show($id)
    {
        return response()->json(Admin::findOrFail($id));
    }

    public function store(Request $request)
    {
        $admin = Admin::create($request->all());
        return response()->json($admin, 201);
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $admin->update($request->all());
        return response()->json($admin);
    }

    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();
        return response()->json(null, 204);
    }
}