<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Validator; // Add this line

// todo - Customize validation error messages if needed by passing a third parameter to the validate method.
// todo - Use $hidden or $appends in the Course model to control the serialized response.

class CourseController extends Controller
{
    //
    public function store(Request $request)
    {
        // $user = Auth::user();

        // method 1:
        // Check if the user has permission to create courses
        // if (!$user || !in_array($user->role_id, [2, 4])) { // Role 2: Instructor, Role 4: Admin
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }
        // method 2:
        if ($request->user()->role_id !== 5) { // 5 for Admin
            return response()->json(['message' => 'Unauthorizedddd, dear user!!'], 403);
        }

        // note - you may want to use the string fot the url if it fails
        // Validate the request
        $validator = Validator::make($request->all(), [
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:255|unique:courses,course_code',
            'instructor_id' => 'required|exists:users,id',
            'assistant_id' => 'nullable|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'capacity' => 'nullable|integer|min:1',
            'visibility' => 'boolean',
            'featured' => 'nullable|boolean',
            'description' => 'nullable|string',
            'about' => 'nullable|string',
            'discussion_group_url' => 'nullable|string',
            'status' => 'in:active,archived,draft',
            'allow_waitlist' => 'boolean',
            'start_date' => 'nullable|date', // 2025-01-01T00:00:00.000Z  Thu Jan 30 2025 00:00:00 GMT+0330 (Iran Standard Time)
            'end_date' => 'nullable|date|after_or_equal:start_date',
            // 'prerequisites' => 'nullable|json',
            'tags' => 'nullable|json',
            'thumbnail_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::create($request->all());

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course,
        ], 201);
    }
}
