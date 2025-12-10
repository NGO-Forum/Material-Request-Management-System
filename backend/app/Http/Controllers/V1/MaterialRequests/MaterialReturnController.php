<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialReturn;
use Illuminate\Http\Request;

class MaterialReturnController extends Controller
{
    public function index()
    {
        $data = MaterialReturn::with([
            'request',
            'returnedBy',
            'itInspector',
            'finalInspector'
        ])->get();

        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:material_requests,id',
            'returned_by' => 'required|exists:users,id',
            'return_date' => 'nullable|date',
            'it_inspected_by' => 'nullable|exists:users,id',
            'it_condition_status' => 'nullable|string',
            'it_remarks' => 'nullable|string',
            'final_confirmed_by' => 'nullable|exists:users,id',
            'admin_remarks' => 'nullable|string',
        ]);

        $model = MaterialReturn::create($validated);

        return response()->json([
            'message' => 'Return record created successfully',
            'data' => $model
        ], 201);
    }

    public function show($id)
    {
        $model = MaterialReturn::with([
            'request',
            'returnedBy',
            'itInspector',
            'finalInspector'
        ])->findOrFail($id);

        return response()->json($model, 200);
    }

    public function update(Request $request, $id)
    {
        $model = MaterialReturn::findOrFail($id);

        $validated = $request->validate([
            'returned_by' => 'nullable|exists:users,id',
            'return_date' => 'nullable|date',
            'it_inspected_by' => 'nullable|exists:users,id',
            'it_condition_status' => 'nullable|string',
            'it_remarks' => 'nullable|string',
            'final_confirmed_by' => 'nullable|exists:users,id',
            'admin_remarks' => 'nullable|string',
        ]);

        $model->update($validated);

        return response()->json([
            'message' => 'Return record updated',
            'data' => $model
        ], 200);
    }

    public function destroy($id)
    {
        MaterialReturn::findOrFail($id)->delete();
        return response()->json(['message' => 'Return record deleted'], 200);
    }
}
