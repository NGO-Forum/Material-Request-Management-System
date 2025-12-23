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

    /**
     * List all requests (filtered by role)
     */
    public function index()
    {
        $user = Auth::user();

        $query = MaterialRequest::with([
            'requester:id,name,email',
            'material:id,name,model,category_id',
            'material.category:id,name'
        ])
            ->select('id', 'requester_id', 'material_id', 'quantity', 'receipt_date', 'purpose', 'status', 'created_at')
            ->latest();

        // Role-based filtering
        if ($user->role->name !== 'Admin') {
            $query->where('requester_id', $user->id);
        }

        $requests = $query->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ], 200);
    }

    /**
     * Create a new material request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|integer|exists:materials,id',
            'quantity'     => 'required|integer|min:1|max:999',
            'receipt_date' => 'required|date|after_or_equal:today',
            'purpose'      => 'nullable|string|max:1000',
        ]);

        $material = Material::findOrFail($validated['material_id']);

        if ($material->qty_remaining < $validated['quantity']) {
            return response()->json([
                'success'  => false,
                'message'  => 'Not enough stock available.',
                'available' => $material->qty_remaining,
                'requested' => $validated['quantity']
            ], 422);
        }

        $materialRequest = MaterialRequest::create([
            'requester_id' => Auth::id(),
            'material_id'  => $validated['material_id'],
            'quantity'     => $validated['quantity'],
            'receipt_date' => $validated['receipt_date'],
            'purpose'      => $validated['purpose'],
            'status'       => 'pending',
        ]);

        // Load relationships for response
        $materialRequest->load(['requester:id,name,email', 'material:id,name,model']);

        return response()->json([
            'success' => true,
            'message' => 'Material request submitted successfully!',
            'data'    => $materialRequest
        ], 201);
    }

    /**
     * Show a single request (with authorization)
     */
    public function show($id)
    {
        $user = Auth::user();

        $request = MaterialRequest::with([
            'requester:id,name,email',
            'material:id,name,model,category_id',
            'material.category:id,name',
            'actions.actor:id,name', // actor = user who performed action
            'issueRecord.issuedBy:id,name', // nested issued_by
            'returnRecord.returnedBy:id,name', // nested returned_by
            'returnRecord.itInspector:id,name' // inspected by
        ])->findOrFail($id);

        // Authorization: Admin can see all, others only their own
        if ($user->role->name !== 'Admin' && $request->requester_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this request.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $request
        ], 200);
    }

    /**
     * Update status (approve/reject/cancel)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();

        // Only Admin or Manager can change status
        if (!in_array($user->role->name, ['Admin', 'Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only Admins or Managers can approve/reject requests.'
            ], 403);
        }

        $materialRequest = MaterialRequest::findOrFail($id);

        $validated = $request->validate([
            'status'  => ['required', Rule::in(['approved', 'rejected', 'cancelled'])],
            'remarks' => 'nullable|string|max:1000'
        ]);

        // Prevent changing status if already issued or returned
        if (in_array($materialRequest->status, ['issued', 'returned', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change status of issued, returned, or cancelled requests.'
            ], 400);
        }

        $materialRequest->status = $validated['status'];
        $materialRequest->save();

        MaterialRequestAction::create([
            'request_id'  => $materialRequest->id,
            'action_by'   => Auth::id(),
            'action_type' => $validated['status'],
            'remarks'     => $validated['remarks'] ?? null,
        ]);

        $materialRequest->load(['requester:id,name,email', 'material:id,name,model']);

        return response()->json([
            'success' => true,
            'message' => "Request has been {$validated['status']} successfully!",
            'data'    => $materialRequest
        ], 200);
    }

    /**
     * Issue material to approved request
     */
    public function issue(Request $request, $id)
    {
        $user = Auth::user();

        // Only Admin or Manager can issue
        if (!in_array($user->role->name, ['Admin', 'Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only Admins or Managers can issue materials.'
            ], 403);
        }

        return DB::transaction(function () use ($request, $id, $user) {
            $materialRequest = MaterialRequest::findOrFail($id);

            if ($materialRequest->status !== 'approved') {
                return response()->json(['success' => false, 'message' => 'Request must be approved first'], 400);
            }

            if (MaterialIssueRecord::where('request_id', $id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Material already issued'], 400);
            }

            $validated = $request->validate([
                'issued_date'          => 'required|date',
                'expected_return_date' => 'nullable|date|after:issued_date',
            ]);

            $material = $materialRequest->material;

            if ($material->qty_remaining < $materialRequest->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock to issue',
                    'available' => $material->qty_remaining,
                    'requested' => $materialRequest->quantity
                ], 400);
            }

            $material->decrement('qty_remaining', $materialRequest->quantity);

            $issue = MaterialIssueRecord::create([
                'request_id'          => $id,
                'issued_by'           => $user->id,
                'issued_date'         => $validated['issued_date'],
                'expected_return_date' => $validated['expected_return_date'] ?? null,
            ]);

            $materialRequest->status = 'issued';
            $materialRequest->save();

            MaterialRequestAction::create([
                'request_id'  => $id,
                'action_by'   => $user->id,
                'action_type' => 'issued',
                'remarks'     => 'Material physically issued'
            ]);

            MaterialStockMovement::create([
                'material_id'   => $material->id,
                'request_id'    => $id,
                'movement_type' => 'issue',
                'quantity'      => $materialRequest->quantity,
                'remarks'       => 'Issued via request #' . $id
            ]);

            $issue->load('issuedBy:id,name');

            return response()->json([
                'success' => true,
                'message' => 'Material issued successfully!',
                'data'    => $issue
            ], 200);
        });
    }

    /**
     * Record return of issued material
     */
    public function returnMaterial(Request $request, $id)
    {
        $user = Auth::user();

        // Only Admin or Manager can record returns
        if (!in_array($user->role->name, ['Admin', 'Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only Admins or Managers can record returns.'
            ], 403);
        }

        return DB::transaction(function () use ($request, $id, $user) {
            $materialRequest = MaterialRequest::findOrFail($id);

            if ($materialRequest->status !== 'issued') {
                return response()->json(['success' => false, 'message' => 'Material must be issued first'], 400);
            }

            if (MaterialReturn::where('request_id', $id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Material already returned'], 400);
            }

            $validated = $request->validate([
                'it_condition_status' => 'required|in:Good,Damaged,Lost',
                'it_remarks'          => 'nullable|string|max:1000',
            ]);

            $return = MaterialReturn::create([
                'request_id'          => $id,
                'returned_by'         => $user->id, // assuming returner is the issuer or admin
                'it_inspected_by'     => $user->id, // inspector is current user (admin/manager)
                'it_condition_status' => $validated['it_condition_status'],
                'it_remarks'          => $validated['it_remarks'],
                'return_date'         => now()->format('Y-m-d H:i:s'),
            ]);

            if ($validated['it_condition_status'] === 'Good') {
                $materialRequest->material->increment('qty_remaining', $materialRequest->quantity);
            }

            $materialRequest->status = 'returned';
            $materialRequest->save();

            MaterialRequestAction::create([
                'request_id'  => $id,
                'action_by'   => $user->id,
                'action_type' => 'returned',
                'remarks'     => $validated['it_remarks'] ?? 'Returned in ' . strtolower($validated['it_condition_status']) . ' condition'
            ]);

            if ($validated['it_condition_status'] === 'Good') {
                MaterialStockMovement::create([
                    'material_id'   => $materialRequest->material_id,
                    'request_id'    => $id,
                    'movement_type' => 'return',
                    'quantity'      => $materialRequest->quantity,
                    'remarks'       => 'Returned in good condition'
                ]);
            }

            $return->load(['returnedBy:id,name', 'itInspector:id,name']);

            return response()->json([
                'success' => true,
                'message' => 'Material returned successfully!',
                'data'    => $return
            ], 200);
        });
    }
}