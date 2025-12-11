<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialRequestController extends Controller
{
    public function __construct()
    {
        // Protect all routes with Sanctum authentication
        $this->middleware('auth:sanctum');
    }

    /**
     * List all requests (with relationships)
     */
    public function index()
    {
        $requests = MaterialRequest::with(['requester', 'material.category'])
            ->latest()
            ->get();

        return response()->json([
            'data' => $requests
        ], 200);
    }

    /**
     * Create new material request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'quantity'    => 'required|integer|min:1|max:999',
            'purpose'     => 'nullable|string|max:1000',
            // status is forced to 'pending' – users cannot set it
        ]);

        // Check if material has enough stock (optional but recommended)
        $material = \App\Models\Material::findOrFail($validated['material_id']);
        if ($material->qty_remaining < $validated['quantity']) {
            return response()->json([
                'message' => 'Not enough stock available.',
                'available' => $material->qty_remaining
            ], 422);
        }

        $requestData = MaterialRequest::create([
            'requester_id' => Auth::id(),                    // ← Real logged-in user
            'material_id'  => $validated['material_id'],
            'quantity'     => $validated['quantity'],
            'purpose'      => $validated['purpose'] ?? null,
            'status'       => 'pending',                     // ← Always starts as pending
        ]);

        // Load relationships for response
        $requestData->load(['requester', 'material']);

        return response()->json([
            'message' => 'Material request created successfully',
            'data'    => $requestData
        ], 201);
    }

    /**
     * Show single request
     */
    public function show($id)
    {
        $request = MaterialRequest::with(['requester', 'material', 'actions.actor'])
            ->findOrFail($id);

        // Optional: Only allow requester or admin to view
        // if (Auth::id() !== $request->requester_id && !Auth::user()->is_admin) {
        //     abort(403);
        // }

        return response()->json($request, 200);
    }

    /**
     * Update request (only admins/managers should do this)
     */
    public function update(Request $request, $id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);

        // Only allow admins or managers to update status
        // Adjust this logic based on your roles
        // if (!Auth::user()->hasRole(['admin', 'manager', 'it_staff'])) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $validated = $request->validate([
            'status'      => 'required|in:approved,rejected,issued,returned,cancelled',
            'manager_id'  => 'nullable|exists:users,id',
            'admin_hr_id' => 'nullable|exists:users,id',
            'it_staff_id' => 'nullable|exists:users,id',
            'remarks'     => 'nullable|string',
        ]);

        $materialRequest->update($validated);

        // Optional: Log action
        $materialRequest->actions()->create([
            'action_by'   => Auth::id(),
            'action_type' => $validated['status'],
            'remarks'     => $validated['remarks'] ?? null,
        ]);

        $materialRequest->load(['requester', 'material']);

        return response()->json([
            'message' => 'Request updated successfully',
            'data'    => $materialRequest
        ], 200);
    }

    /**
     * Delete request (only admins or requester before approval)
     */
    public function destroy($id)
    {
        $request = MaterialRequest::findOrFail($id);

        // Allow deletion only if still pending and by requester
        if ($request->status !== 'pending' || Auth::id() !== $request->requester_id) {
            return response()->json(['message' => 'Cannot delete this request'], 403);
        }

        $request->delete();

        return response()->json(['message' => 'Request deleted successfully'], 200);
    }
}