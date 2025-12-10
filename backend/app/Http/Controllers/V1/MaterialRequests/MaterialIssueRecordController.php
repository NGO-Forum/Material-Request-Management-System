<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialIssueRecord;
use Illuminate\Http\Request;

class MaterialIssueRecordController extends Controller
{
    public function index()
    {
        $data = MaterialIssueRecord::with(['request', 'issuedBy'])->get();
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:material_requests,id',
            'issued_by' => 'required|exists:users,id',
            'issued_date' => 'nullable|date',
            'expected_return_date' => 'nullable|date',
            'actual_return_date' => 'nullable|date',
        ]);

        $model = MaterialIssueRecord::create($validated);

        return response()->json([
            'message' => 'Issue record created successfully',
            'data' => $model
        ], 201);
    }

    public function show($id)
    {
        $model = MaterialIssueRecord::with(['request', 'issuedBy'])->findOrFail($id);
        return response()->json($model, 200);
    }

    public function update(Request $request, $id)
    {
        $model = MaterialIssueRecord::findOrFail($id);

        $validated = $request->validate([
            'issued_by' => 'nullable|exists:users,id',
            'issued_date' => 'nullable|date',
            'expected_return_date' => 'nullable|date',
            'actual_return_date' => 'nullable|date',
        ]);

        $model->update($validated);

        return response()->json([
            'message' => 'Issue record updated',
            'data' => $model
        ], 200);
    }

    public function destroy($id)
    {
        MaterialIssueRecord::findOrFail($id)->delete();
        return response()->json(['message' => 'Issue record deleted'], 200);
    }
}
