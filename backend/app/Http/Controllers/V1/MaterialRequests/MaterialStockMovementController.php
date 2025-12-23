<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialStockMovementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $user = Auth::user();

        $query = MaterialStockMovement::with(['material', 'request.requester']);

        if ($user->role !== 'admin') {
            $query->whereHas('request', function ($q) use ($user) {
                $q->where('requester_id', $user->id);
            });
        }

        $data = $query->latest()->get();

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'request_id' => 'nullable|exists:material_requests,id',
            'movement_type' => 'required|in:issue,return,adjustment',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        $model = MaterialStockMovement::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Stock movement created successfully',
            'data' => $model
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        $model = MaterialStockMovement::with(['material', 'request'])->findOrFail($id);

        if ($user->role !== 'admin' && $model->request && $model->request->requester_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['success' => true, 'data' => $model], 200);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

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
            'success' => true,
            'message' => 'Stock movement updated',
            'data' => $model
        ], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        MaterialStockMovement::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Stock movement deleted'], 200);
    }
}