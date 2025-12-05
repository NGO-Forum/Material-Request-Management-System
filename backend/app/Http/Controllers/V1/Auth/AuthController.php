<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // ===========================
    // REGISTER
    // ===========================
        public function register(Request $request)
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

        // Upload image if provided
        if ($request->hasFile('image_profile')) {
            $path = $request->file('image_profile')->store('user_profiles', 'public');
            $validated['image_profile'] = '/storage/' . $path;
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Create user
        $user = User::create($validated);

        // Create API token
        $token = $user->createToken('api_token')->plainTextToken;

        // Load user relations
        $user->load(['role', 'department']);

        // Convert profile URL
        $user->image_profile = $user->image_profile ? url($user->image_profile) : null;

        return response()->json([
            'message' => 'User registered successfully',
            'token'   => $token,
            'data'    => $user
        ], 201);
    }



    // ===========================
    // LOGIN
    // ===========================
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Create token
        $token = $user->createToken('api_token')->plainTextToken;

        $user->load('role', 'department');
        $user->image_profile = $user->image_profile ? url($user->image_profile) : null;

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'data'    => $user
        ], 200);
    }


    // ===========================
    // LOGOUT (Destroy Token)
    // ===========================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    // ===========================
    // GET AUTH USER
    // ===========================
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('role', 'department');
        $user->image_profile = $user->image_profile ? url($user->image_profile) : null;

        return response()->json($user, 200);
    }
}
