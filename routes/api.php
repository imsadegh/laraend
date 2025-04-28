<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OTPVerificationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseModuleController;
// use App\Http\Controllers\InstructorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentSubmissionController;
use App\Http\Controllers\TuitionController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamAttemptController;
use App\Http\Controllers\ExamAttemptAnswerController;
use App\Http\Controllers\ExamScoreController;
use App\Http\Controllers\ExamQuestionController;
use App\Http\Controllers\QuestionController;


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
// todo: only admin and instructor can create, update, and delete courses
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
    Route::get('/instructors', [CourseController::class, 'getInstructorsNames']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/courses/prerequisites', [CourseController::class, 'getPrerequisites']);
});

// Assignments Management
Route::middleware('auth:api')->group(function () {
    Route::post('/courses/{course}/assignments', [AssignmentController::class, 'store']);
    // Route::get('/courses/{course}/assignments', [AssignmentController::class, 'index']);
    Route::get('/instructor/assignments', [AssignmentController::class, 'index']);
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
    Route::apiResource('exams', ExamController::class);
    //  GET    /exams   → index     | 	GET    /exams/{exam}    → show
    Route::apiResource('questions', QuestionController::class);
    Route::apiResource('exams.questions', ExamQuestionController::class)
        ->parameters(['questions' => 'id']);
    //  GET    /exams/{exam}/questions ➜ index     |  GET    /exams/{exam}/questions/{id} ➜ show

    // attempts nested under exams; `shallow()` keeps nice URLs
    Route::apiResource('exams.attempts', ExamAttemptController::class)
        ->shallow()                                   // GET /attempts/{id} instead of /exams/{exam}/attempts/{id}
        ->only(['store', 'index', 'show', 'update']);
    // list / filter all attempts (instructor & admin)
    Route::get('/exam-attempts', [ExamAttemptController::class, 'index']);

    // extra read-only endpoint for “next unanswered question”
    Route::get(
        'exams/{exam}/attempts/{attempt}/next',
        [ExamAttemptController::class, 'next']
    )->name('attempts.next');

    // single-answer endpoint nested under attempts
    Route::apiResource('attempts.answers', ExamAttemptAnswerController::class)
        ->shallow()
        ->only(['store']);
    // Route::put('/exam-attempts/{attempt}/review', [ExamAttemptController::class, 'review']); // rev an exam attempt (assign score/feedback)

    Route::apiResource('exam-scores', ExamScoreController::class)->only(['index', 'update', 'store']);
});


// Route::fallback(function () {
//     return response()->json(['message' => 'Route not found.'], 404);
// });
