<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Validator; // Add this line
use Illuminate\Http\JsonResponse;

// todo - Customize validation error messages if needed by passing a third parameter to the validate method.
// todo - Use $hidden or $appends in the Course model to control the serialized response.

class CourseController extends Controller
{
    // todo - i should use show function instead of the function that is writen below, for fetch all data of a course at once
    public function getPrerequisites(): JsonResponse
    {
        $prerequisites = Course::select('course_code', 'course_name')
            ->orderBy('course_name')
            ->get();

        return response()->json($prerequisites, 200);
    }

    public function store(Request $request)
    {
        // $user = Auth::user();

        // method 1:
        // Check if the user has permission to create courses
        // if (!$user || !in_array($user->role_id, [2, 4])) { // Role 2: Instructor, Role 4: Admin
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }
        // method 2:
        // if ($request->user()->role_id !== 5) { // 5 for Admin
        //     return response()->json(['message' => 'Unauthorizedddd, dear user!!'], 403);
        // }
        // method 3:
        if (!auth()->check() || (auth()->user()->role->name !== 'admin')) {
            return response()->json(['message' => 'Unauthorized, dear user!'], 403);
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
            'prerequisites' => 'nullable|json',
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

    public function update(Request $request, $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // Check if the authenticated user is the instructor for this course with is_verified true or is the admin
        if (!auth()->check() || (($course->instructor_id != auth()->user()->id || !auth()->user()->is_verified) && auth()->user()->role->name !== 'admin')) {
            return response()->json(['message' => 'Unauthorized or it is not a verifed user.'], 403);
        }

        $request->validate([
            // 'course_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'about' => 'nullable|string',
            'visibility' => 'boolean',
            'status' => 'in:active,archived,draft',
            'discussion_group_url' => 'nullable|string',
            // 'start_date' => 'nullable|date',
            // 'end_date' => 'nullable|date|after_or_equal:start_date',
            // Add any other fields as necessary
        ]);

        $course->update($request->only([
            'description',
            // 'about',
            'visibility',
            'status',
            'discussion_group_url',
            // 'start_date',
            // 'end_date'
        ]));
        return response()->json(['message' => 'Course updated successfully', 'course' => $course], 200);
    }

    public function getInstructorCourses(Request $request)
    {
        // Get the authenticated instructor
        $instructorId = auth()->id();

        // Fetch courses where the instructor is assigned
        $courses = Course::where('instructor_id', $instructorId)
            ->select('id', 'course_name', 'course_code', 'assistant_id', 'status', 'visibility', 'description', 'about', 'discussion_group_url', 'start_date', 'end_date', 'thumbnail_url')
            ->get();

        return response()->json($courses);
    }

    public function show($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // Ensure only the assigned instructor or an admin can fetch this course
        if ($course->instructor_id != auth()->id() && auth()->user()->role->name !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($course);
    }
}
