<?php

namespace App\Http\Controllers\V1\Materials;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MaterialController extends Controller
{
    // List all materials
    public function index()
    {
        $materials = Material::with('category')->get();

        $materials->transform(function ($material) {
            $material->image = $material->image ? url($material->image) : null;
            return $material;
        });

        return response()->json($materials, 200);
    }

    // Create material
    public function store(Request $request)
    {
        return $this->saveMaterial($request);
    }

    // Update material
    public function update(Request $request, $id)
    {
        return $this->saveMaterial($request, $id);
    }

    // Shared save method for create/update
    private function saveMaterial(Request $request, $id = null)
    {
        $isUpdate = !is_null($id);
        $material = $isUpdate ? Material::findOrFail($id) : null;

        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:materials,serial_number' . ($isUpdate ? ',' . $id : ''),
            'qty_stock' => 'required|integer|min:0',
            'qty_issued' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
            'condition' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remarks' => 'nullable|string|max:500',
        ];

        $validated = $request->validate($rules);

        // Auto-calculate remaining stock
        $validated['qty_issued'] = $validated['qty_issued'] ?? 0;
        $validated['qty_remaining'] = $validated['qty_stock'] - $validated['qty_issued'];

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($isUpdate && $material->image) {
                $oldPath = str_replace('/storage/', '', parse_url($material->image, PHP_URL_PATH));
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('image')->store('materials', 'public');
            $validated['image'] = '/storage/' . $path;
        }

        if ($isUpdate) {
            $material->update($validated);
            $material->load('category');
            $material->image = $material->image ? url($material->image) : null;

            return response()->json([
                'message' => 'Material updated successfully',
                'data' => $material
            ], 200);
        } else {
            $material = Material::create($validated);
            $material->load('category');
            $material->image = $material->image ? url($material->image) : null;

            return response()->json([
                'message' => 'Material created successfully',
                'data' => $material
            ], 201);
        }
    }

    // Show single material
    public function show($id)
    {
        $material = Material::with('category')->findOrFail($id);
        $material->image = $material->image ? url($material->image) : null;

        return response()->json($material, 200);
    }

    // Delete material
    public function destroy($id)
    {
        $material = Material::findOrFail($id);

        if ($material->image) {
            $path = str_replace('/storage/', '', parse_url($material->image, PHP_URL_PATH));
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $material->delete();

        return response()->json(['message' => 'Material deleted successfully'], 200);
    }
}
