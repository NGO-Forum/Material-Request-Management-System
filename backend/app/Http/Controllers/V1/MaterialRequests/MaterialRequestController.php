<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;

class MaterialRequestController extends Controller
{
    // List all requests
    public function index()
    {
        $data = MaterialRequest::with(['requester', 'material'])->get();
        return response()->json($data, 200);
    }

    // Create request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'requester_id' => 'required|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'admin_hr_id' => 'nullable|exists:users,id',
            'it_staff_id' => 'nullable|exists:users,id',
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $model = MaterialRequest::create($validated);

        return response()->json([
            'message' => 'Material request created successfully',
            'data' => $model,
        ], 201);
    }

    // Show single request
    public function show($id)
    {
        $model = MaterialRequest::with(['requester','material'])->findOrFail($id);
        return response()->json($model, 200);
    }

    // Update request
    public function update(Request $request, $id)
    {
        $model = MaterialRequest::findOrFail($id);

        $validated = $request->validate([
            'requester_id' => 'nullable|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'admin_hr_id' => 'nullable|exists:users,id',
            'it_staff_id' => 'nullable|exists:users,id',
            'material_id' => 'nullable|exists:materials,id',
            'quantity' => 'nullable|integer|min:1',
            'purpose' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $model->update($validated);

        return response()->json([
            'message' => 'Material request updated successfully',
            'data' => $model,
        ], 200);
    }

    // Delete request
    public function destroy($id)
    {
        MaterialRequest::findOrFail($id)->delete();

        return response()->json(['message' => 'Material request deleted'], 200);
    }
}
