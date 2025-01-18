<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OTPVerificationController;
use App\Http\Controllers\CourseController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/signup', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::post('/otp/send', [OTPVerificationController::class, 'sendOTP']);
Route::post('/otp/verify', [OTPVerificationController::class, 'verifyOTP']);

Route::post('/courses', [CourseController::class, 'store'])->middleware('auth:api');
