<?php

namespace App\Http\Controllers;

use App\Models\UserActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone_number' => 'required|regex:/^09\d{9}$/|unique:users,phone_number',
            // 'username' => 'required|string|max:255|unique:users',
            // 'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id', // Validate role_id against roles table
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Generate a default username if not provided
        $username = $request->input('username', strtolower($request->first_name . '.' . $request->last_name));
        // Ensure the username is unique
        $username = User::where('username', $username)->exists() ? $username . rand(100, 999) : $username;

        // Create the user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'username' => $username, // Assign generated username
            // 'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'role_id' => $request->role_id, // Save role_id
            'role_id' => $request->input('role_id', 1), // Default to 'Student'

        ]);

        // Log the registration activity
        // UserActivityLog::create([
        //     'user_id' => $user->id,
        //     'activity_type' => 'registration',
        //     'activity_details' => 'User registered with role_id: ' . $request->role_id,
        //     'ip_address' => $request->ip(),
        //     'user_agent' => $request->userAgent(),
        // ]);

        return response()->json(['message' => 'User registered successfully!', 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|regex:/^09\d{9}$/',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Attempt to find the user
        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['errors' => ['credentials' => 'Invalid phone number or password.']], 401);
        }

        // Generate an access token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'accessToken' => $token,
            'userData' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone_number' => $user->phone_number,
                'email'=> $user->email,
                'role_id' => $user->role_id,
            ],
            'userAbilityRules' => $user->getAbilityRules(), // Use the model's method
        ], 200);
    }
}
