<?php

namespace App\Http\Controllers;

use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class VideoProxyController extends Controller
{
    protected EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    /**
     * Stream video via secure token
     * GET /api/videos/stream?token={jwt}
     *
     * Returns: 302 redirect to actual video URL (URL not exposed in response body)
     */
    public function stream(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['error' => 'Stream token is required.'], 400);
        }

        try {
            // Validate JWT token
            $decoded = JWTAuth::setToken($token)->getPayload();

            // Check token purpose
            if ($decoded->get('purpose') !== 'video_stream') {
                return response()->json(['error' => 'Invalid token purpose.'], 401);
            }

            // Extract encrypted URL from token payload
            $encryptedUrl = $decoded->get('payload')['encrypted_url'] ?? null;

            if (!$encryptedUrl) {
                return response()->json(['error' => 'Token payload is invalid.'], 401);
            }

            // Decrypt URL
            $url = $this->encryptionService->decryptUrl($encryptedUrl);

            if (!$url) {
                return response()->json(['error' => 'Failed to decrypt video URL.'], 500);
            }

            // Re-validate user is still enrolled in course
            // Prevents access after enrollment is revoked or course access is suspended
            $userId = $decoded->get('user_id');
            $courseId = $decoded->get('course_id');

            if ($userId && $courseId) {
                $isEnrolled = \DB::table('course_enrollments')
                    ->where('user_id', $userId)
                    ->where('course_id', $courseId)
                    ->where('status', 'enrolled')
                    ->exists();

                if (!$isEnrolled) {
                    return response()->json(['error' => 'Access revoked. You are no longer enrolled in this course.'], 403);
                }
            }

            // Return 302 redirect to actual video URL
            // This way the URL is never exposed in the response body, only in the Location header
            return redirect($url)
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0')
                ->header('X-Content-Type-Options', 'nosniff')
                ->header('X-Frame-Options', 'DENY');

        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid or expired token.'], 401);
        } catch (\Exception $e) {
            \Log::error('Video stream failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to process stream token.'], 500);
        }
    }
}
