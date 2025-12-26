<?php

namespace App\Http\Controllers\V1\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'department'])->get();

        // Return full image URL
        $users->transform(function ($user) {
            $user->image_profile = $user->image_profile ? url('/storage/' . $user->image_profile) : null;
            return $user;
        });

        return response()->json($users, 200);
    }

    public function store(Request $request)
    {
        return $this->saveUser($request);
    }

    public function update(Request $request, $id)
    {
        return $this->saveUser($request, $id);
    }

    private function saveUser(Request $request, $id = null)
    {
        $isUpdate = !is_null($id);
        $user = $isUpdate ? User::findOrFail($id) : null;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($isUpdate ? ',' . $id : ''),
            'password' => ($isUpdate ? 'nullable' : 'required') . '|string|min:6',
            'image_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
        ];

        $validated = $request->validate($rules);

        // Prevent multiple Admin users
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole && $validated['role_id'] == $adminRole->id) {
            $existingAdmin = User::where('role_id', $adminRole->id);
            if ($isUpdate) $existingAdmin->where('id', '!=', $id);
            if ($existingAdmin->exists()) {
                throw ValidationException::withMessages([
                    'role_id' => 'Only one Admin user is allowed.'
                ]);
            }
        }

        // Hash password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle image upload
        if ($request->hasFile('image_profile')) {
            // Delete old image if exists
            if ($isUpdate && $user && $user->image_profile) {
                if (Storage::disk('public')->exists($user->image_profile)) {
                    Storage::disk('public')->delete($user->image_profile);
                }
            }

            $path = $request->file('image_profile')->store('user_profiles', 'public');
            $validated['image_profile'] = $path; // store relative path
        }

        // Save user
        if ($isUpdate) {
            $user->update($validated);
            $user->load('role', 'department');
            $user->image_profile = $user->image_profile ? url('/storage/' . $user->image_profile) : null;
            return response()->json([
                'message' => 'User updated successfully',
                'data' => $user
            ], 200);
        } else {
            $user = User::create($validated);
            $user->load('role', 'department');
            $user->image_profile = $user->image_profile ? url('/storage/' . $user->image_profile) : null;
            return response()->json([
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        }
    }

    public function show($id)
    {
        $user = User::with(['role', 'department'])->findOrFail($id);
        $user->image_profile = $user->image_profile ? url('/storage/' . $user->image_profile) : null;
        return response()->json($user, 200);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting the only Admin
        if ($user->role && strtolower($user->role->name) === 'admin') {
            $adminCount = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->count();
            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'Cannot delete the only Admin user.'
                ], 403);
            }
        }

        // Delete user image if exists
        if ($user->image_profile && Storage::disk('public')->exists($user->image_profile)) {
            Storage::disk('public')->delete($user->image_profile);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
