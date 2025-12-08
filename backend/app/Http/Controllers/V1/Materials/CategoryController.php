<?php

namespace App\Http\Controllers\V1\Materials;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    // List all categories
    public function index()
    {
        $categories = Category::with('materials')->get();
        return response()->json($categories, 200);
    }

    // Create category
    public function store(Request $request)
    {
        return $this->saveCategory($request);
    }

    // Update category
    public function update(Request $request, $id)
    {
        return $this->saveCategory($request, $id);
    }

    // Shared save method
    private function saveCategory(Request $request, $id = null)
    {
        $isUpdate = !is_null($id);
        $category = $isUpdate ? Category::findOrFail($id) : null;

        $rules = [
            'name' => 'required|string|max:255|unique:categories,name' . ($isUpdate ? ',' . $id : ''),
            'description' => 'nullable|string|max:500',
        ];

        $validated = $request->validate($rules);

        if ($isUpdate) {
            $category->update($validated);
            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $category
            ], 200);
        } else {
            $category = Category::create($validated);
            return response()->json([
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        }
    }

    // Show single category
    public function show($id)
    {
        $category = Category::with('materials')->findOrFail($id);
        return response()->json($category, 200);
    }

    // Delete category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->materials()->count() > 0) {
            throw ValidationException::withMessages([
                'error' => 'Cannot delete category with materials. Remove or reassign materials first.'
            ]);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
}
