<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    //
    public function store(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:50|unique:courses,course_code',
            'teacher_id' => 'required|exists:users,id',
            'assistant_id' => 'nullable|exists:users,id',
            'instructor_id' => 'nullable|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'capacity' => 'nullable|integer|min:0',
            'visibility' => 'required|boolean',
            'featured' => 'required|boolean',
            'description' => 'nullable|string',
            'about' => 'nullable|string',
            'discussion_group_url' => 'nullable|url',
            'status' => 'required|in:active,archived,draft',
            'allow_waitlist' => 'required|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'prerequisites' => 'nullable|array',
            'tags' => 'nullable|array',
            'thumbnail_url' => 'nullable|url',
        ]);

        // Create the course
        $course = Course::create([
            'course_name' => $validated['course_name'],
            'course_code' => $validated['course_code'],
            'teacher_id' => $validated['teacher_id'],
            'assistant_id' => $validated['assistant_id'] ?? null,
            'instructor_id' => $validated['instructor_id'] ?? null,
            'category_id' => $validated['category_id'],
            'capacity' => $validated['capacity'] ?? null,
            'visibility' => $validated['visibility'],
            'featured' => $validated['featured'],
            'description' => $validated['description'] ?? null,
            'about' => $validated['about'] ?? null,
            'discussion_group_url' => $validated['discussion_group_url'] ?? null,
            'status' => $validated['status'],
            'allow_waitlist' => $validated['allow_waitlist'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'prerequisites' => $validated['prerequisites'] ?? null,
            'tags' => $validated['tags'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
        ]);

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course,
        ], 201);
    }
}
