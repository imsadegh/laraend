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
    //  POST   /exams   → store     |	PUT    /exams/{exam}   → update
    //  DELETE /exams/{exam}   → destroy

    Route::apiResource('questions', QuestionController::class);

    Route::apiResource('exams.questions', ExamQuestionController::class)
        ->parameters(['questions' => 'id']);
    //  GET    /exams/{exam}/questions ➜ index     |  GET    /exams/{exam}/questions/{id} ➜ show
    //  POST   /exams/{exam}/questions ➜ store     |  PUT    /exams/{exam}/questions/{id} ➜ update
    //  DELETE /exams/{exam}/questions/{id} ➜ destroy


    // only when the exam is active
    Route::post('/exams/{exam}/attempts', [ExamAttemptController::class, 'store']); // for students starting/submitting an exam
    Route::get('/instructor/exam-attempts', [ExamAttemptController::class, 'index']); // List exam attempts for courses taught by the instructor
    Route::get('/exam-attempts/{attempt}', [ExamAttemptController::class, 'show']); // Get details of an exam attempt
    Route::put('/exam-attempts/{attempt}', [ExamAttemptController::class, 'update']); // Update an exam attempt (submit answers)

    Route::post('/exam-attempts/{attempt}/answers', [ExamAttemptAnswerController::class, 'store']);
    // Route::put('/exam-attempts/{attempt}/review', [ExamAttemptController::class, 'review']); // rev an exam attempt (assign score/feedback)
    // Optionally, if you handle exam scores separately:
    // Route::get('/exam-scores', [ExamScoreController::class, 'index']); // List exam scores (if needed)
});



// Route::fallback(function () {
//     return response()->json(['message' => 'Route not found.'], 404);
// });
