<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialStockMovement;
use Illuminate\Http\Request;

class MaterialStockMovementController extends Controller
{
    public function index()
    {
        $data = MaterialStockMovement::with(['material', 'request'])->get();
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'request_id' => 'nullable|exists:material_requests,id',
            'movement_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        $model = MaterialStockMovement::create($validated);

        return response()->json([
            'message' => 'Stock movement created successfully',
            'data' => $model
        ], 201);
    }

    public function show($id)
    {
        $model = MaterialStockMovement::with(['material', 'request'])->findOrFail($id);
        return response()->json($model, 200);
    }

    public function update(Request $request, $id)
    {
        $model = MaterialStockMovement::findOrFail($id);

        $validated = $request->validate([
            'material_id' => 'nullable|exists:materials,id',
            'request_id' => 'nullable|exists:material_requests,id',
            'movement_type' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        $model->update($validated);

        return response()->json([
            'message' => 'Stock movement updated',
            'data' => $model
        ], 200);
    }

    public function destroy($id)
    {
        MaterialStockMovement::findOrFail($id)->delete();
        return response()->json(['message' => 'Stock movement deleted'], 200);
    }
}
