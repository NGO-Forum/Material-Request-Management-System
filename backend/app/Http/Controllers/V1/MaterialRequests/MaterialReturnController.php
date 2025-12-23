<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $user = Auth::user();

        $query = MaterialReturn::with([
            'request.material',
            'request.requester:id,name,email',
            'returnedBy:id,name',
            'itInspector:id,name',
            'finalInspector:id,name'
        ]);

        if ($user->role !== 'admin') {
            // Non-admins only see returns for their own requests
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
            'request_id' => 'required|exists:material_requests,id|unique:material_returns,request_id',
            'returned_by' => 'required|exists:users,id',
            'it_inspected_by' => 'required|exists:users,id',
            'it_condition_status' => 'required|in:Good,Damaged,Lost',
            'it_remarks' => 'nullable|string',
            'return_date' => 'required|date',
        ]);

        $record = MaterialReturn::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Material returned successfully',
            'data' => $record->load(['returnedBy', 'itInspector'])
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();

        $model = MaterialReturn::with([
            'request.material',
            'request.requester',
            'returnedBy',
            'itInspector',
            'finalInspector'
        ])->findOrFail($id);

        if ($user->role !== 'admin' && $model->request->requester_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['success' => true, 'data' => $model], 200);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Only admin can update return records'], 403);
        }

        $model = MaterialReturn::findOrFail($id);

        $validated = $request->validate([
            'returned_by' => 'nullable|exists:users,id',
            'return_date' => 'nullable|date',
            'it_inspected_by' => 'nullable|exists:users,id',
            'it_condition_status' => 'nullable|in:Good,Damaged,Lost',
            'it_remarks' => 'nullable|string',
            'final_confirmed_by' => 'nullable|exists:users,id',
            'admin_remarks' => 'nullable|string',
        ]);

        $model->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Return record updated',
            'data' => $model->load(['returnedBy', 'itInspector'])
        ], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        ;
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Only admin can delete return records'], 403);
        }

        MaterialReturn::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Return record deleted'], 200);
    }
}