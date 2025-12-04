<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Constants\RoleConstant;

class CourseVideoLinkController extends Controller
{
    protected EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    /**
     * Add a video link to a course module
     * POST /api/courses/{course}/modules/{module}/add-video
     */
    public function addVideo(Request $request, Course $course, CourseModule $module): JsonResponse
    {
        $user = Auth::user();

        // Validate: course exists and user is instructor of this course
        if (!$this->isInstructorOfCourse($user, $course)) {
            return response()->json(['error' => 'Unauthorized. You are not an instructor of this course.'], 403);
        }

        // Validate: module belongs to this course
        if ($module->course_id !== $course->id) {
            return response()->json(['error' => 'Module does not belong to this course.'], 404);
        }

        // Validate request input
        $validated = $request->validate([
            'video_url' => 'required|url',
            'video_title' => 'required|string|max:255',
            'estimated_duration_seconds' => 'required|integer|min:1',
            'video_source' => 'nullable|string|max:50',
        ]);

        // Validate URL
        $urlValidation = $this->validateVideoUrl($validated['video_url']);
        if (!$urlValidation['valid']) {
            return response()->json(['error' => $urlValidation['message']], 422);
        }

        // Encrypt and store
        try {
            $module->encrypted_video_url = $validated['video_url']; // Will be auto-encrypted via mutator
            $module->video_title = $validated['video_title'];
            $module->estimated_duration_seconds = $validated['estimated_duration_seconds'];
            $module->video_source = $validated['video_source'] ?? 'external';
            $module->video_added_by = $user->id;
            $module->video_added_at = now();
            $module->save();

            return response()->json([
                'video_id' => $module->id,
                'module_id' => $module->id,
                'title' => $module->video_title,
                'estimated_duration_seconds' => $module->estimated_duration_seconds,
                'video_source' => $module->video_source,
                'added_at' => $module->video_added_at->toIso8601String(),
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Video storage failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to store video.'], 500);
        }
    }

    /**
     * Get video stream token for playback
     * GET /api/courses/{course}/modules/{module}/video-stream-token
     */
    public function getStreamToken(Request $request, Course $course, CourseModule $module): JsonResponse
    {
        $user = Auth::user();

        // Validate: module belongs to this course
        if ($module->course_id !== $course->id) {
            return response()->json(['error' => 'Module does not belong to this course.'], 404);
        }

        // Validate: user is enrolled in this course
        if (!$this->isEnrolledInCourse($user, $course)) {
            return response()->json(['error' => 'You are not enrolled in this course.'], 403);
        }

        // Validate: module has a video
        if (!$module->encrypted_video_url) {
            return response()->json(['error' => 'This module does not have a video.'], 404);
        }

        try {
            // Get the encrypted URL (this will be auto-decrypted via accessor)
            $decryptedUrl = $module->encrypted_video_url;

            if (!$decryptedUrl) {
                return response()->json(['error' => 'Failed to decrypt video URL.'], 500);
            }

            // Create JWT token with encrypted URL payload
            // We re-encrypt it for the token to avoid storing plain text
            $encryptedForToken = $this->encryptionService->encryptUrl($decryptedUrl);

            $payload = [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'module_id' => $module->id,
                'purpose' => 'video_stream',
                'payload' => [
                    'encrypted_url' => $encryptedForToken,
                ],
            ];

            $ttl = config('videos.token_ttl_minutes', 5);
            // Create JWT token with custom claims and TTL
            $customClaims = array_merge($payload, ['exp' => now()->addMinutes($ttl)->timestamp]);
            $token = JWTAuth::claims($customClaims)->fromUser($user);

            return response()->json([
                'stream_token' => $token,
                'expires_in' => $ttl * 60,
                'video_title' => $module->video_title,
            ]);
        } catch (\Exception $e) {
            \Log::error('Token generation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to generate token.'], 500);
        }
    }

    /**
     * Update a video link in a course module
     * PUT /api/courses/{course}/modules/{module}/video
     */
    public function updateVideo(Request $request, Course $course, CourseModule $module): JsonResponse
    {
        $user = Auth::user();

        // Same authorization as addVideo
        if (!$this->isInstructorOfCourse($user, $course)) {
            return response()->json(['error' => 'Unauthorized. You are not an instructor of this course.'], 403);
        }

        if ($module->course_id !== $course->id) {
            return response()->json(['error' => 'Module does not belong to this course.'], 404);
        }

        $validated = $request->validate([
            'video_url' => 'required|url',
            'video_title' => 'required|string|max:255',
            'estimated_duration_seconds' => 'required|integer|min:1',
            'video_source' => 'nullable|string|max:50',
        ]);

        $urlValidation = $this->validateVideoUrl($validated['video_url']);
        if (!$urlValidation['valid']) {
            return response()->json(['error' => $urlValidation['message']], 422);
        }

        try {
            $module->encrypted_video_url = $validated['video_url'];
            $module->video_title = $validated['video_title'];
            $module->estimated_duration_seconds = $validated['estimated_duration_seconds'];
            $module->video_source = $validated['video_source'] ?? 'external';
            $module->video_added_by = $user->id;
            $module->video_added_at = now();
            $module->save();

            return response()->json([
                'video_id' => $module->id,
                'module_id' => $module->id,
                'title' => $module->video_title,
                'estimated_duration_seconds' => $module->estimated_duration_seconds,
                'video_source' => $module->video_source,
                'updated_at' => $module->video_added_at->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update video: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a video link from a course module
     * DELETE /api/courses/{course}/modules/{module}/video
     */
    public function deleteVideo(Request $request, Course $course, CourseModule $module): JsonResponse
    {
        $user = Auth::user();

        if (!$this->isInstructorOfCourse($user, $course)) {
            return response()->json(['error' => 'Unauthorized. You are not an instructor of this course.'], 403);
        }

        if ($module->course_id !== $course->id) {
            return response()->json(['error' => 'Module does not belong to this course.'], 404);
        }

        try {
            $module->encrypted_video_url = null;
            $module->video_title = null;
            $module->estimated_duration_seconds = null;
            $module->video_source = null;
            $module->video_added_at = null;
            $module->video_added_by = null;
            $module->save();

            return response()->json(['message' => 'Video link removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete video: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Validate a video URL
     * Checks: HTTPS, domain whitelisted, accessible
     */
    private function validateVideoUrl(string $url): array
    {
        // Check HTTPS
        if (config('videos.validation.require_https', true)) {
            if (!str_starts_with($url, 'https://')) {
                return ['valid' => false, 'message' => 'Video URL must use HTTPS protocol.'];
            }
        }

        // Check domain whitelist
        $allowedDomains = config('videos.allowed_domains', []);
        $urlHost = parse_url($url, PHP_URL_HOST);
        
        if (!$urlHost) {
            return ['valid' => false, 'message' => 'Invalid URL format.'];
        }

        $isDomainAllowed = false;
        foreach ($allowedDomains as $domain) {
            if ($urlHost === $domain || str_ends_with($urlHost, '.' . $domain)) {
                $isDomainAllowed = true;
                break;
            }
        }

        if (!$isDomainAllowed) {
            return ['valid' => false, 'message' => 'Video domain is not whitelisted.'];
        }

        // Check accessibility via HEAD request
        try {
            $client = new Client();
            $timeout = config('videos.validation.head_request_timeout', 10);
            
            $client->head($url, [
                'timeout' => $timeout,
                'allow_redirects' => true,
            ]);

            return ['valid' => true];
        } catch (RequestException $e) {
            return ['valid' => false, 'message' => 'Video URL is not accessible (unreachable or invalid).'];
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => 'Failed to validate video URL.'];
        }
    }

    /**
     * Check if user is an instructor of the course
     */
    private function isInstructorOfCourse(User $user, Course $course): bool
    {
        // Admin can do anything
        if ($user->role_id == RoleConstant::ADMIN) {
            return true;
        }

        // Instructor (role_id = 2) must be the creator or assigned to the course
        if ($user->role_id == RoleConstant::INSTRUCTOR) {
            return $course->created_by == $user->id;
        }

        return false;
    }

    /**
     * Check if user is enrolled in the course
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

        // Check enrollment
        return $course->enrollments()
            ->where('user_id', $user->id)
            ->where('status', 'enrolled')
            ->exists();
    }
}
