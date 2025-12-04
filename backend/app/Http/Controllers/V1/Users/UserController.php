<?php

namespace App\Http\Controllers\V1\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // List all users
    public function index()
    {
        $users = User::with(['role', 'department'])->get();

        $users->transform(function ($user) {
            $user->image_profile = $user->image_profile ? url($user->image_profile) : null;
            return $user;
        });

        return response()->json($users, 200);
    }

    // Create a new user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'image_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role_id' => 'nullable|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        if ($request->hasFile('image_profile')) {
            $path = $request->file('image_profile')->store('user_profiles', 'public');
            $validated['image_profile'] = '/storage/' . $path;
        }

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        $user->load('role', 'department');
        $user->image_profile = $user->image_profile ? url($user->image_profile) : null;

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    // Show a single user
    public function show($id)
    {
        $user = User::with(['role', 'department'])->findOrFail($id);
        $user->image_profile = $user->image_profile ? url($user->image_profile) : null;

        return response()->json($user, 200);
    }

    // Update a user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'image_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role_id' => 'nullable|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('image_profile')) {
            if ($user->image_profile) {
                $oldPath = str_replace('/storage/', '', $user->image_profile);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image_profile')->store('user_profiles', 'public');
            $validated['image_profile'] = '/storage/' . $path;
        }

        $user->update($validated);

        $user->load('role', 'department');
        $user->image_profile = $user->image_profile ? url($user->image_profile) : null;

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

    // Delete a user
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->image_profile && Storage::exists(str_replace('/storage/', 'public/', $user->image_profile))) {
            Storage::delete(str_replace('/storage/', 'public/', $user->image_profile));
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }
}