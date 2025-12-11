<?php

namespace App\Http\Controllers\V1\MaterialRequests;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use App\Models\Material;
use App\Models\MaterialRequestAction;
use App\Models\MaterialIssueRecord;
use App\Models\MaterialReturn;
use App\Models\MaterialStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MaterialRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $requests = MaterialRequest::with([
                'requester:id,name,email',
                'material:id,name,model,category_id',
                'material.category:id,name'
            ])
            ->select('id', 'requester_id', 'material_id', 'quantity', 'receipt_date', 'purpose', 'status', 'created_at')
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $requests], 200);
    }

    public function store(Request $request)
    {
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

        return response()->json([
            'success' => true,
            'message' => 'Material request submitted successfully!',
            'data'    => $materialRequest->load(['requester:id,name,email', 'material:id,name,model'])
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

        return response()->json(['success' => true, 'data' => $request], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'cancelled'])],
            'remarks' => 'nullable|string|max:1000'
        ]);

        $materialRequest->status = $validated['status'];
        $materialRequest->save();

        MaterialRequestAction::create([
            'request_id'  => $materialRequest->id,
            'action_by'   => Auth::id(),
            'action_type' => $validated['status'],
            'remarks'     => $validated['remarks'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Request has been {$validated['status']} successfully!",
            'data'    => $materialRequest->load(['requester', 'material'])
        ], 200);
    }

    // NEW: ISSUE MATERIAL (Perfect & Safe)
    public function issue(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $materialRequest = MaterialRequest::findOrFail($id);

            if ($materialRequest->status !== 'approved') {
                return response()->json(['success' => false, 'message' => 'Request must be approved first'], 400);
            }

            if (MaterialIssueRecord::where('request_id', $id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Material already issued'], 400);
            }

            $validated = $request->validate([
                'issued_date' => 'required|date',
                'expected_return_date' => 'nullable|date|after:issued_date',
                'issued_by' => 'required|exists:users,id',
            ]);

            // Deduct stock
            $material = $materialRequest->material;
            if ($material->qty_remaining < $materialRequest->quantity) {
                return response()->json(['success' => false, 'message' => 'Not enough stock to issue'], 400);
            }

            $material->decrement('qty_remaining', $materialRequest->quantity);

            // Create issue record
            $issue = MaterialIssueRecord::create([
                'request_id' => $id,
                'issued_by' => $validated['issued_by'],
                'issued_date' => $validated['issued_date'],
                'expected_return_date' => $validated['expected_return_date'],
            ]);

            // Update request status
            $materialRequest->status = 'issued';
            $materialRequest->save();

            // Log action
            MaterialRequestAction::create([
                'request_id' => $id,
                'action_by' => $validated['issued_by'],
                'action_type' => 'issued',
                'remarks' => 'Material physically issued'
            ]);

            // Stock movement
            MaterialStockMovement::create([
                'material_id' => $material->id,
                'request_id' => $id,
                'movement_type' => 'issue',
                'quantity' => $materialRequest->quantity,
                'remarks' => 'Issued via request #' . $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Material issued successfully!',
                'data' => $issue->load('issuedBy')
            ], 200);
        });
    }

    // NEW: RETURN MATERIAL (Perfect & Safe)
    public function returnMaterial(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $materialRequest = MaterialRequest::findOrFail($id);

            if ($materialRequest->status !== 'issued') {
                return response()->json(['success' => false, 'message' => 'Material must be issued first'], 400);
            }

            if (MaterialReturn::where('request_id', $id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Material already returned'], 400);
            }

            $validated = $request->validate([
                'it_condition_status' => 'required|in:Good,Damaged,Lost',
                'it_remarks' => 'nullable|string',
                'returned_by' => 'required|exists:users,id',
                'it_inspected_by' => 'required|exists:users,id',
            ]);

            $return = MaterialReturn::create([
                'request_id' => $id,
                'returned_by' => $validated['returned_by'],
                'it_inspected_by' => $validated['it_inspected_by'],
                'it_condition_status' => $validated['it_condition_status'],
                'it_remarks' => $validated['it_remarks'],
                'return_date' => now()->format('Y-m-d'),
            ]);

            // Return stock only if condition is Good
            if ($validated['it_condition_status'] === 'Good') {
                $materialRequest->material->increment('qty_remaining', $materialRequest->quantity);
            }

            // Update request status
            $materialRequest->status = 'returned';
            $materialRequest->save();

            // Log action
            MaterialRequestAction::create([
                'request_id' => $id,
                'action_by' => $validated['returned_by'],
                'action_type' => 'returned',
                'remarks' => $validated['it_remarks']
            ]);

            // Stock movement
            if ($validated['it_condition_status'] === 'Good') {
                MaterialStockMovement::create([
                    'material_id' => $materialRequest->material_id,
                    'request_id' => $id,
                    'movement_type' => 'return',
                    'quantity' => $materialRequest->quantity,
                    'remarks' => 'Returned in good condition'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Material returned successfully!',
                'data' => $return->load(['returnedBy', 'itInspector'])
            ], 200);
        });
    }
}