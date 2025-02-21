<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OTPVerificationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AssignmentController;

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
// Route::post('/courses', [CourseController::class, 'store'])->middleware('auth:api');
// Route::put('/courses/{course}', [CourseController::class, 'update'])->middleware('auth:api');
// Route::middleware('auth:api')->group(function () {
//     Route::get('/instructor/courses', [CourseController::class, 'getInstructorCourses']);
//     Route::get('/courses/{course}', [CourseController::class, 'show']);
// });
Route::middleware('auth:api')->group(function () {
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{course}', [CourseController::class, 'update']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::get('/instructor/courses', [CourseController::class, 'getInstructorCourses']);
});

// Instructor, Categories, and Prerequisites
// Route::get('/instructors', [InstructorController::class, 'index'])->middleware('auth:api');
// Route::get('/categories', [CategoryController::class, 'index'])->middleware('auth:api');
// Route::get('/courses/prerequisites', [CourseController::class, 'getPrerequisites'])->middleware('auth:api');
Route::middleware('auth:api')->group(function () {
    Route::get('/instructors', [InstructorController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/courses/prerequisites', [CourseController::class, 'getPrerequisites']);
});

// Assignments Management
// Route::post('/courses/{course}/assignments', [AssignmentController::class, 'store'])->middleware('auth:api');
// Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->middleware('auth:api');
// Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->middleware('auth:api');
// Route::get('/courses/{course}/assignments', [AssignmentController::class, 'index']);
// Route::get('/assignments/{assignment}', [AssignmentController::class, 'show']);
Route::middleware('auth:api')->group(function () {
    Route::post('/courses/{course}/assignments', [AssignmentController::class, 'store']);
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update']);
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy']);
    Route::get('/courses/{course}/assignments', [AssignmentController::class, 'index']);
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show']);
});

// Route::fallback(function () {
//     return response()->json(['message' => 'Route not found.'], 404);
// });
