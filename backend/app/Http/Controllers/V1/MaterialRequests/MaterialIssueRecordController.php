<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialIssueRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialIssueRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $user = Auth::user();

        $query = MaterialIssueRecord::with([
            'request.material',
            'request.requester:id,name,email',
            'issuedBy:id,name'
        ]);

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
        if (!in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'request_id' => 'required|exists:material_requests,id|unique:material_issue_records,request_id',
            'issued_by' => 'required|exists:users,id',
            'issued_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after:issued_date',
        ]);

        $record = MaterialIssueRecord::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Material issued successfully',
            'data' => $record->load('issuedBy')
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        $model = MaterialIssueRecord::with(['request', 'issuedBy'])->findOrFail($id);

        if ($user->role !== 'admin' && $model->request->requester_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['success' => true, 'data' => $model], 200);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Only admin can update issue records'], 403);
        }

        $model = MaterialIssueRecord::findOrFail($id);

        $validated = $request->validate([
            'issued_by' => 'nullable|exists:users,id',
            'issued_date' => 'nullable|date',
            'expected_return_date' => 'nullable|date',
        ]);

        $model->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Issue record updated',
            'data' => $model
        ], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Only admin can delete issue records'], 403);
        }

        MaterialIssueRecord::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Issue record deleted'], 200);
    }
}