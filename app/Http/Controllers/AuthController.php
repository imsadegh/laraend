<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\UserActivityLog;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Cache;
use App\Constants\RoleConstant;
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
            // 'role_id' => 'required|exists:roles,id', // Validate role_id against roles table
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Generate a default username if not provided
        $username = $request->input('username', strtolower($request->first_name . '.' . $request->last_name));
        // Ensure the username is unique
        $username = User::where('username', $username)->exists() ? $username . rand(100, 999) : $username;
        // Create the fullname by merging first_name and last_name
        $fullname = $request->first_name . ' ' . $request->last_name;

        // Create the user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'full_name' => $fullname, // Assign generated fullname
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
            // 'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Attempt to find the user
        // $user = User::where('phone_number', $request->phone_number)->first();
        // $user = User::where('email', $request->email)->first();
        $user = User::where('phone_number', $request->phone_number)->with('role')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['errors' => ['credentials' => 'Invalid phone number or password.']], 401);
        }

        // Generate an access token
        // $token = $user->createToken('auth_token')->plainTextToken;
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token'], 500);
        }

        return response()->json([
            'message' => 'Login successful.',
            'accessToken' => $token,
            'userData' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'phone_number' => $user->phone_number,
                'email'=> $user->email,
                'avatar' => $user->avatar,
                'role_id' => $user->role_id,
                'role' => $user->role->name,
            ],
            'userAbilityRules' => $user->getAbilityRules(), // Use the model's method
        ], 200);
    }

    /**
     * Deep link login for mobile app
     * POST /api/auth/deep-link-login
     *
     * Validates a deep link token and returns fresh auth credentials
     * This allows mobile app to trade deep link token for regular JWT + user data
     */
    public function deepLinkLogin(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'deep_link_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            // Validate JWT signature and expiry
            $decoded = JWTAuth::setToken($request->input('deep_link_token'))->getPayload();

            // Verify token type and purpose
            if ($decoded->get('type') !== 'deep_link') {
                return response()->json(['errors' => ['token' => 'Invalid or expired token.']], 401);
            }

            if ($decoded->get('purpose') !== 'video_playback') {
                return response()->json(['errors' => ['token' => 'Invalid or expired token.']], 401);
            }

            // CRITICAL: Check if token has already been used (replay prevention)
            $jti = $decoded->get('jti');
            if (!$jti || Cache::has("deep_link_used:{$jti}")) {
                \Log::warning('Deep link token replay attempt', [
                    'jti' => $jti,
                    'ip' => request()->ip(),
                ]);
                return response()->json(['errors' => ['token' => 'Invalid or expired token.']], 401);
            }

            // Mark token as used (expires after token TTL)
            $ttl = config('videos.token_ttl_minutes', 5);
            Cache::put("deep_link_used:{$jti}", true, now()->addMinutes($ttl));

            // Extract claims from token
            $userId = $decoded->get('user_id');
            $courseId = $decoded->get('course_id');
            $moduleId = $decoded->get('module_id');

            if (!$userId || !$courseId || !$moduleId) {
                return response()->json(['errors' => ['token' => 'Invalid or expired token.']], 401);
            }

            // Fetch user
            $user = User::with('role')->findOrFail($userId);

            // CRITICAL: Re-validate enrollment (prevents access after unenrollment)
            $course = Course::findOrFail($courseId);
            if (!$this->isEnrolledInCourse($user, $course)) {
                \Log::warning('Deep link login denied - user not enrolled', [
                    'user_id' => $user->id,
                    'course_id' => $courseId,
                ]);
                return response()->json(['errors' => ['token' => 'Invalid or expired token.']], 401);
            }

            // Generate fresh JWT token (regular login token with longer TTL)
            $token = JWTAuth::fromUser($user);

            // Log this as a login event (for analytics)
            \Log::info('Deep link login successful', [
                'user_id' => $user->id,
                'course_id' => $courseId,
                'method' => 'deep_link',
            ]);

            return response()->json([
                'message' => 'Deep link login successful.',
                'accessToken' => $token,
                'userData' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'username' => $user->username,
                    'phone_number' => $user->phone_number,
                    'email'=> $user->email,
                    'avatar' => $user->avatar,
                    'role_id' => $user->role_id,
                    'role' => $user->role->name,
                ],
                'userAbilityRules' => $user->getAbilityRules(),
            ], 200);

        } catch (JWTException $e) {
            \Log::error('Deep link login failed', ['error' => 'Invalid or expired token']);
            return response()->json(['errors' => ['token' => 'Invalid or expired token.']], 401);
        } catch (\Exception $e) {
            \Log::error('Deep link login error', ['error' => $e->getMessage()]);
            return response()->json(['errors' => ['token' => 'Failed to process login.']], 500);
        }
    }

    /**
     * Check if user is enrolled in course
     * Shared helper used by AuthController, DeepLinkController, and CourseVideoLinkController
     */
    protected function isEnrolledInCourse(User $user, Course $course): bool
    {
        // Admin can access any course
        if ($user->role_id == RoleConstant::ADMIN) {
            return true;
        }

        // Instructors of the course can access it
        if ($user->role_id == RoleConstant::INSTRUCTOR && $course->created_by == $user->id) {
            return true;
        }

        // Check enrollment for students
        return $course->enrollments()
            ->where('user_id', $user->id)
            ->where('status', 'enrolled')
            ->exists();
    }

    /**
     * Get the authenticated user's profile
     */
    public function profile(Request $request)
    {
        $user = auth()->user();

        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'username' => $user->username,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'melli_code' => $user->melli_code,
            'birth_date' => $user->birth_date,
            'sex' => $user->sex,
            'address' => $user->address,
            'city' => $user->city,
            'zip_code' => $user->zip_code,
            'avatar' => $user->avatar,
            'role_id' => $user->role_id,
            'role' => $user->role->name ?? null,
        ]);
    }

    /**
     * Update the authenticated user's profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:50',
            'last_name' => 'sometimes|string|max:50',
            'email' => 'sometimes|nullable|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|regex:/^09\d{9}$/|unique:users,phone_number,' . $user->id,
            'melli_code' => 'sometimes|nullable|string|max:10',
            'birth_date' => 'sometimes|nullable|date',
            'sex' => 'sometimes|nullable|in:male,female',
            'address' => 'sometimes|nullable|string|max:500',
            'city' => 'sometimes|nullable|string|max:100',
            'zip_code' => 'sometimes|nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Update full_name if first or last name changed
        if (isset($data['first_name']) || isset($data['last_name'])) {
            $firstName = $data['first_name'] ?? $user->first_name;
            $lastName = $data['last_name'] ?? $user->last_name;
            $data['full_name'] = $firstName . ' ' . $lastName;
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'melli_code' => $user->melli_code,
                'birth_date' => $user->birth_date,
                'sex' => $user->sex,
                'address' => $user->address,
                'city' => $user->city,
                'zip_code' => $user->zip_code,
                'avatar' => $user->avatar,
                'role_id' => $user->role_id,
                'role' => $user->role->name ?? null,
            ],
        ]);
    }

    /**
     * Update the authenticated user's avatar
     */
    public function updateAvatar(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && file_exists(storage_path('app/public/' . $user->avatar))) {
                unlink(storage_path('app/public/' . $user->avatar));
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
            $user->save();

            return response()->json([
                'message' => 'Avatar updated successfully',
                'avatar' => asset('storage/' . $avatarPath),
            ]);
        }

        return response()->json(['message' => 'No avatar file provided'], 400);
    }

    /**
     * Change the authenticated user's password
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['errors' => ['current_password' => ['Current password is incorrect']]], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }
}
