<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OTPVerificationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\TuitionController;
use App\Http\Controllers\CourseEnrollmentController;

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
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show']);
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update']);
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy']);

});

// Tuition Payment
Route::middleware('auth:api')->group(function () {
    Route::post('/tuition/pay', [TuitionController::class, 'pay']);
    Route::get('/tuition/summary', [TuitionController::class, 'summary']);
});

//
Route::middleware(['auth:api', 'role:admin'])->group(function () {
// Route::middleware('auth:api')->group(function () {
    // Enroll a student (already existing)
    Route::post('/admin/enrollments', [CourseEnrollmentController::class, 'enrollStudent']);

     // New endpoints for admin enrollment management
     Route::get('/admin/enrollments', [CourseEnrollmentController::class, 'index']);
     Route::put('/admin/enrollments/{id}', [CourseEnrollmentController::class, 'update']);
});

// Route::fallback(function () {
//     return response()->json(['message' => 'Route not found.'], 404);
// });
