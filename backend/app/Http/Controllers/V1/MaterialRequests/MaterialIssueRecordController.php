<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialIssueRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MaterialIssueRecordController extends Controller
{
    public function index()
    {
        $data = MaterialIssueRecord::with(['request', 'issuedBy'])->get();
        return response()->json($data, 200);
    }

    // MaterialIssueRecordController.php
    public function store(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:material_requests,id|unique:material_issue_records,request_id',
            'issued_by' => 'required|exists:users,id',
            'issued_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after:issued_date',
        ]);

        $record = MaterialIssueRecord::create($request->all());

        return response()->json([
            'message' => 'Material issued successfully',
            'data' => $record->load('issuedBy')
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

        if ($request->filled('expected_return_date') && $request->filled('issued_date')) {
            if (strtotime($request->expected_return_date) <= strtotime($request->issued_date)) {
                throw ValidationException::withMessages(['expected_return_date' => 'The expected return date must be a date after the issue date.']);
            }
        }

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