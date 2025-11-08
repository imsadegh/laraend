<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Str;
use App\Constants\RoleConstant;

class DeepLinkController extends Controller
{
    /**
     * Generate a deep link token for mobile app video playback
     * GET /api/deep-link/watch?course_id={courseId}&module_id={moduleId}
     *
     * Returns a deep link with embedded JWT token that allows:
     * 1. Direct navigation to video in mobile app
     * 2. Automatic login if user not already authenticated
     * 3. Token expires in 5 minutes (cannot be reused after expiry)
     */
    public function getWatchLink(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Validate request parameters
        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'module_id' => 'required|integer|exists:course_modules,id',
        ]);

        $courseId = $validated['course_id'];
        $moduleId = $validated['module_id'];

        // Fetch course and module
        $course = Course::findOrFail($courseId);
        $module = CourseModule::findOrFail($moduleId);

        // Validate: module belongs to course
        if ($module->course_id !== $course->id) {
            return response()->json([
                'error' => 'Module does not belong to this course.',
            ], 404);
        }

        // Validate: user is enrolled in course
        if (!$this->isEnrolledInCourse($user, $course)) {
            return response()->json([
                'error' => 'You are not enrolled in this course.',
            ], 403);
        }

        // Validate: module has a video
        if (!$module->encrypted_video_url) {
            return response()->json([
                'error' => 'This module does not have a video.',
            ], 404);
        }

        try {
            // Generate a deep-link specific JWT token (5 minute TTL)
            // Add jti (JWT ID) claim for single-use token tracking
            $payload = [
                'jti' => (string) Str::uuid(), // Unique token ID for replay prevention
                'user_id' => $user->id,
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'type' => 'deep_link',
                'purpose' => 'video_playback',
            ];

            $ttl = config('videos.token_ttl_minutes', 5);
            
            // Create token with custom claims and TTL
            $token = JWTAuth::factory()->setTTL($ttl)->claims($payload)->fromUser($user);

            // Build the deep link URL
            $deepLink = 'app://watch?' . http_build_query([
                'token' => $token,
                'course_id' => $courseId,
                'module_id' => $moduleId,
            ]);

            // Fallback URLs for app not installed
            $fallbackUrl = $this->getFallbackUrl();

            // Log deep link generation for audit trail
            \Log::info('Deep link generated', [
                'user_id' => $user->id,
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'ip' => request()->ip(),
            ]);

            return response()->json([
                'deep_link' => $deepLink,
                'fallback_url' => $fallbackUrl,
                'token_expires_in' => $ttl * 60, // Convert to seconds
                'module_title' => $module->video_title,
            ], 200);

        } catch (JWTException $e) {
            \Log::error('Deep link token generation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to generate deep link token.',
            ], 500);
        }
    }

    /**
     * Check if user is enrolled in course
     */
    private function isEnrolledInCourse(User $user, Course $course): bool
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
     * Get fallback URL for when app is not installed
     * Can be configured per platform or return a general app store link
     */
    private function getFallbackUrl(): string
    {
        // For now, return Play Store link
        // In production, could detect platform and return appropriate store link
        return config('deep_links.fallback_url', 'https://play.google.com/store/apps/details?id=com.hakimyar.hekmat_sara');
    }
}
