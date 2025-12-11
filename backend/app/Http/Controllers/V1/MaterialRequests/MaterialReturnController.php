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

    // MaterialReturnController.php
    public function store(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:material_requests,id|unique:material_returns,request_id',
            'returned_by' => 'required|exists:users,id',
            'it_inspected_by' => 'required|exists:users,id',
            'it_condition_status' => 'required|in:Good,Damaged,Lost',
            'it_remarks' => 'nullable|string',
            'return_date' => 'required|date',
        ]);

        $record = MaterialReturn::create($request->all());

        return response()->json([
            'message' => 'Material returned successfully',
            'data' => $record->load(['returnedBy', 'itInspector'])
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