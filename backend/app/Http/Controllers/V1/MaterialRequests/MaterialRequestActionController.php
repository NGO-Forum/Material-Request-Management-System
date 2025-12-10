<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequestAction;
use Illuminate\Http\Request;

class MaterialRequestActionController extends Controller
{
    public function index()
    {
        $data = MaterialRequestAction::with(['request', 'actor'])->get();
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:material_requests,id',
            'action_by' => 'required|exists:users,id',
            'action_type' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $model = MaterialRequestAction::create($validated);

        return response()->json([
            'message' => 'Material request action logged',
            'data' => $model
        ], 201);
    }

    public function show($id)
    {
        $model = MaterialRequestAction::with(['request', 'actor'])->findOrFail($id);
        return response()->json($model, 200);
    }

    public function update(Request $request, $id)
    {
        $model = MaterialRequestAction::findOrFail($id);

        $validated = $request->validate([
            'action_by' => 'nullable|exists:users,id',
            'action_type' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $model->update($validated);

        return response()->json([
            'message' => 'Material request action updated',
            'data' => $model
        ], 200);
    }

    public function destroy($id)
    {
        MaterialRequestAction::findOrFail($id)->delete();
        return response()->json(['message' => 'Material request action deleted'], 200);
    }
}
