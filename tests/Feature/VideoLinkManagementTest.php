<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseModule;
use App\Services\EncryptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class VideoLinkManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $instructor;
    private User $student;
    private Course $course;
    private CourseModule $module;
    private EncryptionService $encryptionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->encryptionService = app(EncryptionService::class);

        // Create test users
        $this->admin = User::factory()->create(['role_id' => 1]);
        $this->instructor = User::factory()->create(['role_id' => 2]);
        $this->student = User::factory()->create(['role_id' => 3]);

        // Create course owned by instructor
        $this->course = Course::factory()->create(['created_by' => $this->instructor->id]);

        // Create module in course
        $this->module = CourseModule::factory()->create(['course_id' => $this->course->id]);

        // Enroll student in course
        $this->course->enrollments()->create([
            'user_id' => $this->student->id,
            'status' => 'enrolled',
        ]);
    }

    // ============ Add Video Tests ============

    public function test_instructor_can_add_video_to_module()
    {
        $response = $this->actingAs($this->instructor, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => 'https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4',
                'video_title' => 'Introduction Video',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['video_id', 'module_id', 'title', 'estimated_duration_seconds', 'video_source', 'added_at']);
        
        $this->module->refresh();
        $this->assertNotNull($this->module->encrypted_video_url);
    }

    public function test_student_cannot_add_video()
    {
        $response = $this->actingAs($this->student, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => 'https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4',
                'video_title' => 'Video',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        $response->assertStatus(403);
    }

    public function test_http_url_rejected()
    {
        $response = $this->actingAs($this->instructor, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => 'http://example.com/video.mp4', // HTTP, not HTTPS
                'video_title' => 'Video',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Video URL must use HTTPS protocol.']);
    }

    public function test_non_whitelisted_domain_rejected()
    {
        $response = $this->actingAs($this->instructor, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => 'https://evil-domain.com/video.mp4', // Not whitelisted
                'video_title' => 'Video',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Video domain is not whitelisted.']);
    }

    // ============ Encryption Tests ============

    public function test_video_url_encrypted_in_database()
    {
        $videoUrl = 'https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4';

        $this->actingAs($this->instructor, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => $videoUrl,
                'video_title' => 'Video',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        $this->module->refresh();
        
        // Encrypted URL should be different from original
        $this->assertNotEquals($videoUrl, $this->module->attributes['encrypted_video_url']);
        
        // Accessor should decrypt it correctly
        $this->assertEquals($videoUrl, $this->module->encrypted_video_url);
    }

    public function test_encryption_service_works()
    {
        $url = 'https://file-examples.com/test.mp4';
        
        $encrypted = $this->encryptionService->encryptUrl($url);
        $decrypted = $this->encryptionService->decryptUrl($encrypted);

        $this->assertNotEquals($url, $encrypted);
        $this->assertEquals($url, $decrypted);
    }

    // ============ Authorization Tests ============

    public function test_student_cannot_get_token_if_not_enrolled()
    {
        // Create another student not enrolled
        $otherStudent = User::factory()->create(['role_id' => 3]);

        // Add video first
        $this->actingAs($this->instructor, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => 'https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4',
                'video_title' => 'Video',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        $response = $this->actingAs($otherStudent, 'api')
            ->get("/api/courses/{$this->course->id}/modules/{$this->module->id}/video-stream-token");

        $response->assertStatus(403);
    }

    // ============ Stream Token Tests ============

    public function test_student_can_get_stream_token_when_enrolled()
    {
        // Add video first
        $this->actingAs($this->instructor, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => 'https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4',
                'video_title' => 'Video Title',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        $response = $this->actingAs($this->student, 'api')
            ->get("/api/courses/{$this->course->id}/modules/{$this->module->id}/video-stream-token");

        $response->assertStatus(200);
        $response->assertJsonStructure(['stream_token', 'expires_in', 'video_title']);
        $response->assertJsonPath('video_title', 'Video Title');
    }

    public function test_stream_token_contains_no_plain_url()
    {
        $videoUrl = 'https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4';

        // Add video
        $this->actingAs($this->instructor, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => $videoUrl,
                'video_title' => 'Video',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        $response = $this->actingAs($this->student, 'api')
            ->get("/api/courses/{$this->course->id}/modules/{$this->module->id}/video-stream-token");

        $tokenData = $response->json();
        $token = $tokenData['stream_token'];

        // Token should not contain plain URL (JWT structure: header.payload.signature)
        $this->assertFalse(strpos($token, $videoUrl) !== false);
    }

    // ============ Update Video Tests ============

    public function test_instructor_can_update_video()
    {
        // Add initial video
        $this->actingAs($this->instructor, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => 'https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4',
                'video_title' => 'Original Title',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        // Update video
        $response = $this->actingAs($this->instructor, 'api')
            ->put("/api/courses/{$this->course->id}/modules/{$this->module->id}/video", [
                'video_url' => 'https://vimeo.com/123456789',
                'video_title' => 'Updated Title',
                'estimated_duration_seconds' => 800,
                'video_source' => 'vimeo',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('title', 'Updated Title');
        $response->assertJsonPath('estimated_duration_seconds', 800);
    }

    // ============ Delete Video Tests ============

    public function test_instructor_can_delete_video()
    {
        // Add video first
        $this->actingAs($this->instructor, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => 'https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4',
                'video_title' => 'Video',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        $response = $this->actingAs($this->instructor, 'api')
            ->delete("/api/courses/{$this->course->id}/modules/{$this->module->id}/video");

        $response->assertStatus(200);

        $this->module->refresh();
        $this->assertNull($this->module->encrypted_video_url);
        $this->assertNull($this->module->video_title);
    }

    // ============ Module Not Found Tests ============

    public function test_invalid_module_returns_404()
    {
        $invalidModuleId = 99999;

        $response = $this->actingAs($this->instructor, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$invalidModuleId}/add-video", [
                'video_url' => 'https://file-examples.com/video.mp4',
                'video_title' => 'Video',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        // Laravel's route model binding will return 404
        $response->assertStatus(404);
    }

    // ============ Admin Permissions Tests ============

    public function test_admin_can_add_video_to_any_course()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->post("/api/courses/{$this->course->id}/modules/{$this->module->id}/add-video", [
                'video_url' => 'https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4',
                'video_title' => 'Admin Video',
                'estimated_duration_seconds' => 600,
                'video_source' => 'external',
            ]);

        $response->assertStatus(201);
    }
}
