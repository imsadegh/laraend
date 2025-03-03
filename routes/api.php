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
Route::middleware('auth:api')->group(function () {
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::put('/courses/{course}', [CourseController::class, 'update']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::get('/instructor/courses', [CourseController::class, 'getInstructorCourses']);
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

// Route::fallback(function () {
//     return response()->json(['message' => 'Route not found.'], 404);
// });
