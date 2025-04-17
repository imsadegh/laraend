<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OTPVerificationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseModuleController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentSubmissionController;
use App\Http\Controllers\TuitionController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamAttemptController;
use App\Http\Controllers\ExamScoreController;


Route::middleware(['auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});

// Auth Routes
Route::post('/signup', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
// OTP Routes
Route::post('/otp/send', [OTPVerificationController::class, 'sendOTP']);
Route::post('/otp/verify', [OTPVerificationController::class, 'verifyOTP']);

// Course Management
// Note: only admin and instructor can create, update, and delete courses
Route::middleware('auth:api')->group(function () {
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::put('/courses/{course}', [CourseController::class, 'update']);
    Route::get('/courses/{course}', [CourseController::class, 'show']); // get info of course for editting(admin and instructor). get info of course for viewing(student)
    Route::get('/instructor/courses', [CourseController::class, 'getInstructorCourses']);
    Route::get('/student/courses', [CourseController::class, 'getEnrolledCourses']);
    // Route::get('/courses/{id}', [CourseController::class, 'getCourseById']);
});

// Course Module Management
Route::middleware('auth:api')->group(function () {
    Route::get('/courses/{course}/modules', [CourseModuleController::class, 'index'])
        ->where('course', '[0-9]+');
    Route::post('/courses/{course}/modules', [CourseModuleController::class, 'store'])
        ->where('course', '[0-9]+');
    Route::get('/modules/{module}', [CourseModuleController::class, 'show'])
        ->where('module', '[0-9]+');
    Route::put('/modules/{module}', [CourseModuleController::class, 'update'])
        ->where('module', '[0-9]+');
    Route::delete('/modules/{module}', [CourseModuleController::class, 'destroy'])
        ->where('module', '[0-9]+');
});

// Instructor, Categories, and Prerequisites
Route::middleware('auth:api')->group(function () {
    Route::get('/instructors', [InstructorController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/courses/prerequisites', [CourseController::class, 'getPrerequisites']);
});

// Assignments Management
Route::middleware('auth:api')->group(function () {
    // Route::get('/courses/{course}/assignments', [AssignmentController::class, 'index']);
    Route::get('/instructor/assignments', [AssignmentController::class, 'index']);
    Route::post('/courses/{course}/assignments', [AssignmentController::class, 'store']);
    // Route::get('/assignments/{assignment}', [AssignmentController::class, 'show']);
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])
        ->where('assignment', '[0-9]+');
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update']);
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy']);

    Route::post('/assignments/{assignment}/submissions', [AssignmentSubmissionController::class, 'store'])
        ->where('assignment', '[0-9]+');
    Route::get('/instructor/assignment-submissions', [AssignmentSubmissionController::class, 'index']);
    Route::put('/instructor/assignment-submissions/{id}/review', [AssignmentSubmissionController::class, 'review']);

    Route::get('/courses/{course}/assignments', [AssignmentController::class, 'getCourseAssignments'])
        ->where('course', '[0-9]+');

});

// Tuition Payment
Route::middleware('auth:api')->group(function () {
    Route::post('/tuition/pay', [TuitionController::class, 'pay']);
    Route::get('/tuition/summary', [TuitionController::class, 'summary']);
});

// Course Enrollment
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Route::middleware('auth:api')->group(function () {
    // Enroll a student (already existing)
    Route::post('/admin/enrollments', [CourseEnrollmentController::class, 'enrollStudent']);

    // New endpoints for admin enrollment management
    Route::get('/admin/enrollments', [CourseEnrollmentController::class, 'index']);
    Route::put('/admin/enrollments/{id}', [CourseEnrollmentController::class, 'update']);
});

// Exams endpoints (for instructors to create, update, list, or delete exams)
Route::middleware('auth:api')->group(function () {
    Route::post('/exams', [ExamController::class, 'store']); // Create a new exam
    Route::get('/exams', [ExamController::class, 'index']); // List all exams (optionally filter by course)
    Route::get('/exams/{exam}', [ExamController::class, 'show']); // Get exam details
    Route::put('/exams/{exam}', [ExamController::class, 'update']); // Update an exam
    Route::delete('/exams/{exam}', [ExamController::class, 'destroy']); // Delete an exam

    // Exam Attempt endpoints (for students starting/submitting an exam)
    Route::post('/exams/{exam}/attempts', [ExamAttemptController::class, 'store']); // Start a new exam attempt
    Route::put('/exam-attempts/{attempt}', [ExamAttemptController::class, 'update']); // Update an exam attempt (submit answers)
    Route::get('/exam-attempts/{attempt}', [ExamAttemptController::class, 'show']); // Get details of an exam attempt

    // Instructor Review endpoints (for instructors to review exam attempts)
    Route::get('/instructor/exam-attempts', [ExamAttemptController::class, 'index']); // List exam attempts for courses taught by the instructor
    Route::put('/exam-attempts/{attempt}/review', [ExamAttemptController::class, 'review']); // Review an exam attempt (assign score/feedback)

    // Optionally, if you handle exam scores separately:
    Route::get('/exam-scores', [ExamScoreController::class, 'index']); // List exam scores (if needed)
});

// Route::fallback(function () {
//     return response()->json(['message' => 'Route not found.'], 404);
// });
