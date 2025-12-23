<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequestAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialRequestActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $user = Auth::user();

        $query = MaterialRequestAction::with(['request.requester', 'actor:id,name']);

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

        $validated = $request->validate([
            'request_id' => 'required|exists:material_requests,id',
            'action_by' => 'required|exists:users,id',
            'action_type' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $model = MaterialRequestAction::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Material request action logged',
            'data' => $model
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        $model = MaterialRequestAction::with(['request', 'actor'])->findOrFail($id);

        if ($user->role !== 'admin' && $model->request->requester_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['success' => true, 'data' => $model], 200);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Only admin can update actions'], 403);
        }

        $model = MaterialRequestAction::findOrFail($id);

        $validated = $request->validate([
            'action_by' => 'nullable|exists:users,id',
            'action_type' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $model->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Material request action updated',
            'data' => $model
        ], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Only admin can delete actions'], 403);
        }

        MaterialRequestAction::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Material request action deleted'], 200);
    }
}