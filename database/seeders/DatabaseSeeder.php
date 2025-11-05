<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with test data for all features
     *
     * This seeder populates the database with realistic test data including:
     * - Roles (Admin, Instructor, Student)
     * - Users (with different roles)
     * - Categories
     * - Courses (with different instructors)
     * - Course Modules (text articles and videos)
     * - Videos (with encrypted URLs for security testing)
     * - Exams and Questions
     * - Course Enrollments
     * - Assignments
     * - Watch Time Tracking (for analytics)
     * - Tuition History
     *
     * Usage:
     *   php artisan migrate:fresh --seed
     *   php artisan db:seed
     *
     * Test Data Structure:
     * - Admin User: ID 1 (role_id = 1)
     * - Instructor: ID 2 (role_id = 2)
     * - Students: ID 3+ (role_id = 3)
     * - Course 1: Created by Admin, with video modules and exams
     * - Course 2: Created by Instructor
     * - Enrolled Students: Student (ID 3) in all courses
     */
    public function run(): void
    {
        // ============ CORE SETUP ============
        // Run migrations if needed
        // \Artisan::call('migrate:fresh');

        // ============ FOUNDATION DATA ============
        // Roles: Admin (1), Instructor (2), Student (3), Custom (5)
        $this->call(RoleSeeder::class);

        // Users: Admin, Instructors, Students
        $this->call(UserSeeder::class);

        // ============ COURSE STRUCTURE ============
        // Categories for course classification
        $this->call(CategorySeeder::class);

        // Courses with metadata
        $this->call(CourseSeeder::class);

        // Course Modules (text + videos)
        $this->call(CourseModuleSeeder::class);

        // ============ VIDEO CONTENT (Phase 1) ============
        // Add encrypted video URLs to modules
        // Tests: Video encryption, stream tokens, secure playback
        $this->call(CourseModuleVideoSeeder::class);

        // ============ ASSIGNMENTS & SUBMISSIONS ============
        $this->call(AssignmentSeeder::class);
        $this->call(AssignmentSubmissionSeeder::class);

        // ============ EXAMS & QUESTIONS ============
        // Exams with multiple questions (MCQ, True/False)
        // Tests: Exam creation, question banks, attempts
        // $this->call(ExamSeeder::class);

        // ============ ENROLLMENTS ============
        // Student enrollments in courses
        $this->call(CourseEnrollmentSeeder::class);

        // ============ WATCH TIME TRACKING (Phase 3) ============
        // Student watch progress on video modules
        // Tests: Analytics, progress tracking, completion rates
        // $this->call(CourseWatchTimeSeeder::class);

        // ============ PAYMENTS ============
        $this->call(TuitionHistorySeeder::class);
    }
}

/**
 * ============================================================================
 * TEST DATA QUERIES - Use these in php artisan tinker or database tools
 * ============================================================================
 *
 * ## 1. AUTHENTICATION TESTS
 * --------
 * Test login endpoint: POST /api/auth/login
 * {
 *   "phone_number": "09121234567",
 *   "password": "Sadegh@123"
 * }
 *
 * ## 2. VIDEO MANAGEMENT TESTS (Phase 1)
 * --------
 *
 * # Get all videos in a course
 * SELECT * FROM course_modules
 * WHERE course_id = 1 AND encrypted_video_url IS NOT NULL;
 *
 * # Get module with video details
 * SELECT id, course_id, title, video_title, estimated_duration_seconds, video_source
 * FROM course_modules
 * WHERE id = 2;
 *
 * # Test stream token endpoint (as authenticated student)
 * GET /api/courses/1/modules/2/video-stream-token
 *
 * # Test video proxy redirect (after getting stream token)
 * GET /api/videos/stream?token={JWT_TOKEN}
 *
 * # Add new video (as instructor)
 * POST /api/courses/1/modules/2/add-video
 * {
 *   "video_url": "https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4",
 *   "video_title": "Test Video",
 *   "estimated_duration_seconds": 600,
 *   "video_source": "external"
 * }
 *
 * # Update video (as instructor)
 * PUT /api/courses/1/modules/2/video
 *
 * # Delete video (as instructor)
 * DELETE /api/courses/1/modules/2/video
 *
 * ## 3. COURSE & MODULE TESTS
 * --------
 *
 * # Get all courses
 * GET /api/courses
 *
 * # Get student's enrolled courses
 * GET /api/student/courses
 *
 * # Get instructor's courses
 * GET /api/instructor/courses
 *
 * # Get course modules
 * GET /api/courses/1/modules
 *
 * # Get specific module details
 * GET /api/modules/2
 *
 * # Get course details (for editing)
 * GET /api/courses/1
 *
 * ## 4. EXAM & QUESTION TESTS
 * --------
 *
 * # Get all exams
 * SELECT * FROM exams WHERE course_id = 1;
 *
 * # Get exam details with questions
 * SELECT e.*, q.id as question_id, q.question_text, q.type
 * FROM exams e
 * JOIN exam_questions eq ON e.id = eq.exam_id
 * JOIN questions q ON eq.question_id = q.id
 * WHERE e.id = 1;
 *
 * # Create exam attempt
 * POST /api/exams/1/attempts
 *
 * # Submit exam answer
 * POST /api/attempts/{attempt_id}/answers
 * {
 *   "question_id": 1,
 *   "answer": "ب"
 * }
 *
 * # Get exam attempts (as instructor)
 * GET /api/exam-attempts?course_id=1
 *
 * # Get specific exam attempt
 * GET /api/attempts/{attempt_id}
 *
 * ## 5. ENROLLMENT & COURSE ACCESS TESTS
 * --------
 *
 * # Get user enrollments
 * SELECT * FROM course_enrollments WHERE user_id = 3;
 *
 * # Check if student is enrolled
 * SELECT COUNT(*) FROM course_enrollments
 * WHERE user_id = 3 AND course_id = 1 AND status = 'enrolled';
 *
 * # Admin: Get all enrollments
 * GET /api/admin/enrollments
 *
 * # Admin: Update enrollment status
 * PUT /api/admin/enrollments/{id}
 * { "status": "completed" }
 *
 * ## 6. ASSIGNMENT TESTS
 * --------
 *
 * # Get course assignments
 * GET /api/courses/1/assignments
 *
 * # Get all assignments (instructor view)
 * GET /api/instructor/assignments
 *
 * # Get assignment details
 * GET /api/assignments/1
 *
 * # Submit assignment
 * POST /api/assignments/1/submissions
 * { "submission_text": "My solution..." }
 *
 * # Get submissions (instructor)
 * GET /api/instructor/assignment-submissions
 *
 * # Review submission
 * PUT /api/instructor/assignment-submissions/1/review
 * { "grade": 90, "feedback": "Great work!" }
 *
 * ## 7. WATCH TIME TRACKING TESTS (Phase 3)
 * --------
 *
 * # Get watch progress
 * GET /api/course-watch-time/2
 *
 * # Record watch session
 * POST /api/course-watch-time
 * {
 *   "course_module_id": 2,
 *   "watch_time_seconds": 120,
 *   "last_position": 120,
 *   "session_id": "uuid",
 *   "events": [
 *     {"type": "play", "position": 0, "timestamp": 1699200000},
 *     {"type": "pause", "position": 120, "timestamp": 1699200120}
 *   ]
 * }
 *
 * # Get course watch stats (instructor)
 * GET /api/courses/1/watch-stats
 *
 * # Query watch data
 * SELECT u.first_name, m.title, cwt.watch_time_seconds, cwt.last_position
 * FROM course_watch_time cwt
 * JOIN users u ON cwt.user_id = u.id
 * JOIN course_modules m ON cwt.course_module_id = m.id
 * WHERE cwt.course_module_id = 2;
 *
 * ## 8. USER PROFILE TESTS
 * --------
 *
 * # Get current user profile
 * GET /api/user
 *
 * # Get user profile (returns authenticated user)
 * GET /api/profile
 *
 * ## 9. DATABASE INSPECTION
 * --------
 *
 * # Count test users
 * SELECT role_id, COUNT(*) as count FROM users GROUP BY role_id;
 *
 * # List all encrypted videos
 * SELECT id, title, video_title, video_source, encrypted_video_url
 * FROM course_modules
 * WHERE encrypted_video_url IS NOT NULL;
 *
 * # Check student enrollment
 * SELECT c.course_name, u.first_name, e.status, e.created_at
 * FROM course_enrollments e
 * JOIN courses c ON e.course_id = c.id
 * JOIN users u ON e.user_id = u.id
 * ORDER BY e.created_at DESC;
 *
 * # Get exam answer stats
 * SELECT eq.exam_id, COUNT(*) as attempts
 * FROM exam_attempt_answers eaa
 * JOIN exam_questions eq ON eaa.question_id = eq.question_id
 * GROUP BY eq.exam_id;
 *
 * # Watch time summary
 * SELECT
 *   m.title,
 *   COUNT(DISTINCT cwt.user_id) as students_watched,
 *   AVG(cwt.watch_time_seconds) as avg_watch_seconds,
 *   MAX(cwt.watch_time_seconds) as max_watch_seconds
 * FROM course_watch_time cwt
 * JOIN course_modules m ON cwt.course_module_id = m.id
 * GROUP BY m.id, m.title;
 *
 * ## 10. TINKER COMMANDS
 * --------
 * php artisan tinker
 *
 * # Get user by ID
 * $user = App\Models\User::find(1);
 *
 * # Get user courses
 * $user = App\Models\User::find(3);
 * $user->enrollments()->with('course')->get();
 *
 * # Test encryption
 * $service = app(App\Services\EncryptionService::class);
 * $url = 'https://example.com/video.mp4';
 * $encrypted = $service->encryptUrl($url);
 * $decrypted = $service->decryptUrl($encrypted);
 *
 * # Get video module
 * $module = App\Models\CourseModule::find(2);
 * $module->encrypted_video_url; // Auto-decrypts via accessor
 *
 * # Get course with modules
 * $course = App\Models\Course::with('modules')->find(1);
 * $course->modules()->where('type', 'video')->get();
 *
 * ## 11. TEST CREDENTIALS
 * --------
 * Admin:      phone: 09121234567, password: Sadegh@123 (role_id: 1)
 * Instructor: phone: 09129876543, password: Pass@123   (role_id: 2)
 * Student:    phone: 09111111111, password: Pass@123   (role_id: 3)
 *
 * Note: Check UserSeeder.php for exact credentials
 */
