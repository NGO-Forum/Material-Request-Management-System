<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // --------------------------
    // REGISTER
    // --------------------------
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'image_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:500',
            'role_id'       => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        // Allow only one admin
        $adminRole = \App\Models\Role::where('name', 'Admin')->first();
        if ($adminRole && $validated['role_id'] == $adminRole->id) {
            if (User::where('role_id', $adminRole->id)->exists()) {
                throw ValidationException::withMessages([
                    'role_id' => 'An Admin user already exists. Only one Admin is allowed.'
                ]);
            }
        }

        // Upload profile image
        if ($request->hasFile('image_profile')) {
            $path = $request->file('image_profile')->store('user_profiles', 'public');
            $validated['image_profile'] = '/storage/' . $path;
        }

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Delete any old tokens for safety
        $user->tokens()->delete();

        $token = $user->createToken('api_token')->plainTextToken;

        $user->load(['role', 'department']);
        $user->image_profile = $user->image_profile ? url($user->image_profile) : null;

        return response()->json([
            'message' => 'User registered successfully',
            'token'   => $token,
            'data'    => $user
        ], 201);
    }

    // --------------------------
    // LOGIN
    // --------------------------
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        // Revoke all previous tokens to prevent session overlap
        $user->tokens()->delete();

        $token = $user->createToken('api_token')->plainTextToken;

        $user->load('role', 'department');
        $user->image_profile = $user->image_profile ? url($user->image_profile) : null;

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'data'    => $user
        ], 200);
    }

    // --------------------------
    // GET CURRENT AUTH USER
    // --------------------------
    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->load('role', 'department');
        $user->image_profile = $user->image_profile ? url($user->image_profile) : null;

        return response()->json($user, 200);
    }

    // --------------------------
    // LOGOUT
    // --------------------------
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            // Delete the current access token
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}
