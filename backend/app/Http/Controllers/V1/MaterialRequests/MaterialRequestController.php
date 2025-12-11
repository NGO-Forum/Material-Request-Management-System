<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use App\Models\Material;
use App\Models\MaterialRequestAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MaterialRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $requests = MaterialRequest::with(['requester:id,name,email', 'material:id,name,model,category_id', 'material.category:id,name'])
            ->select('id', 'requester_id', 'material_id', 'quantity', 'receipt_date', 'purpose', 'status', 'created_at')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $requests
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id'   => 'required|integer|exists:materials,id',
            'quantity'      => 'required|integer|min:1|max:999',
            'receipt_date'  => 'required|date|after_or_equal:today',
            'purpose'       => 'nullable|string|max:1000',
        ], [
            'material_id.required'  => 'Please select a material.',
            'material_id.exists'    => 'The selected material does not exist.',
            'quantity.required'     => 'Quantity is required.',
            'quantity.min'          => 'Quantity must be at least 1.',
            'receipt_date.required' => 'Please select when you need the material (Required By date).',
            'receipt_date.after_or_equal' => 'Receipt date cannot be in the past.',
        ]);

        $material = Material::findOrFail($validated['material_id']);

        if ($material->qty_remaining < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available.',
                'available' => $material->qty_remaining,
                'requested' => $validated['quantity']
            ], 422);
        }

        $materialRequest = MaterialRequest::create([
            'requester_id'  => Auth::id(),
            'material_id'   => $validated['material_id'],
            'quantity'      => $validated['quantity'],
            'receipt_date'  => $validated['receipt_date'],
            'purpose'       => $validated['purpose'],
            'status'        => 'pending',
        ]);

        $materialRequest->load(['requester:id,name,email', 'material:id,name,model']);

        return response()->json([
            'success' => true,
            'message' => 'Material request submitted successfully!',
            'data'    => $materialRequest
        ], 201);
    }

    public function show($id)
    {
        $request = MaterialRequest::with([
                'requester:id,name,email',
                'material:id,name,model',
                'material.category',
                'actions.actor:id,name'
            ])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $request
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);

        if ($materialRequest->status !== 'pending' || Auth::id() !== $materialRequest->requester_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own pending requests.'
            ], 403);
        }

        $validated = $request->validate([
            'material_id'   => 'required|integer|exists:materials,id',
            'quantity'      => 'required|integer|min:1|max:999',
            'receipt_date'  => 'required|date|after_or_equal:today',
            'purpose'       => 'nullable|string|max:1000',
        ]);

        $material = Material::findOrFail($validated['material_id']);
        if ($material->qty_remaining < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock.',
                'available' => $material->qty_remaining
            ], 422);
        }

        $materialRequest->update($validated);
        $materialRequest->load(['requester:id,name,email', 'material:id,name,model']);

        return response()->json([
            'success' => true,
            'message' => 'Request updated successfully',
            'data' => $materialRequest
        ]);
    }

    public function destroy($id)
    {
        $request = MaterialRequest::findOrFail($id);

        if ($request->status !== 'pending' || Auth::id() !== $request->requester_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own pending requests.'
            ], 403);
        }

        $request->delete();

        return response()->json([
            'success' => true,
            'message' => 'Request deleted successfully.'
        ], 200);
    }

    /**
     * NEW METHOD: Approve / Reject / Cancel Request
     */
    public function updateStatus(Request $request, $id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'cancelled'])],
            'remarks' => 'nullable|string|max:1000'
        ]);

        $newStatus = $validated['status'];

        // Optional: Only allow admin/manager to approve/reject
        // if (!Auth::user()->hasRole(['admin', 'manager'])) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        // }

        $materialRequest->status = $newStatus;
        $materialRequest->save();

        // Log the action
        MaterialRequestAction::create([
            'request_id'  => $materialRequest->id,
            'action_by'   => Auth::id(),
            'action_type' => $newStatus,
            'remarks'     => $validated['remarks'] ?? null,
        ]);

        $materialRequest->load(['requester:id,name,email', 'material:id,name,model']);

        return response()->json([
            'success' => true,
            'message' => "Request has been {$newStatus} successfully!",
            'data'    => $materialRequest
        ], 200);
    }
}