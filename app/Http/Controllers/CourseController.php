<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Validator; // Add this line
use Illuminate\Http\JsonResponse;
use App\Models\CourseEnrollment;

// todo - Customize validation error messages if needed by passing a third parameter to the validate method.
// todo - Use $hidden or $appends in the Course model to control the serialized response.

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Fetch only courses that are visible (e.g., visibility = true)
        // You may also want to filter by 'status' = 'active' if that makes sense for your LMS

        $query = Course::query();

        // For instance, only fetch visible if '?visible=1' is in the query
        if ($request->boolean('visible', false)) {
            $query->where('visibility', true);
        }

        // Additional filters like status, category, etc. can go here.
        if ($request->boolean('active', false)) {
            $query->where('status', 'active');
        }
        $courses = $query->get();
        return response()->json($courses);
    }

    public function show($id)
    {
        // Eager load instructor to have their data available
        $course = Course::with('instructor')->find($id);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $user = auth()->user();

        // If the user is an admin or the assigned instructor, return full details
        if ($user->role->name === 'admin' || $course->instructor_id == $user->id) {
            return response()->json($course);
        }

        // Otherwise, assume the user is a student. Check enrollment with status "enrolled"
        $enrollment = CourseEnrollment::where('course_id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'enrolled')
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Unauthorized. You are not enrolled in this course.'], 403);
        }

        // Return only a subset of fields for a student
        $courseData = (array) $course->only(['course_name', 'description', 'about', 'discussion_group_url', 'enrolled_students_count', 'skill_level', 'total_lectures', 'lecture_length', 'language', 'is_captions', 'table_of_content', 'thumbnail_url']);
        $instructor = $course->instructor;
        $courseData['instructor_name'] = $instructor ? trim($instructor->first_name . ' ' . $instructor->last_name) : null;
        $courseData['instructor_pfp'] = $instructor ? $instructor->avatar : null;

        return response()->json($courseData);
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
            'course_name'   => 'required|string|max:255',
            'course_code'   => 'required|string|max:255|unique:courses,course_code',
            'instructor_id' => 'required|exists:users,id',
            'assistant_id'  => 'nullable|exists:users,id',
            'category_id'   => 'required|exists:categories,id',
            'capacity'      => 'nullable|integer|min:1',
            'visibility'    => 'boolean',
            'featured'      => 'nullable|boolean',
            'description'   => 'nullable|string',
            'about'         => 'nullable|string',
            'discussion_group_url' => 'nullable|string',
            'status'        => 'in:active,archived,draft',
            'skill_level'   => 'in:beginner,intermediate,advanced',
            'is_free'       => 'boolean',
            'total_lectures' => 'nullable|integer|min:1',
            'lecture_length' => 'nullable|integer|min:1',
            'total_quizzes'  => 'nullable|integer|min:1',
            'total_assignments' => 'nullable|integer|min:1',
            'total_resources'   => 'nullable|integer|min:1',
            'language'      => 'in:en,fr,ar,fa',
            'is_captions'      => 'boolean',
            'is_certificate'   => 'boolean',
            'is_quiz'          => 'boolean',
            'is_assignment'    => 'boolean',
            'table_of_content' => 'nullable|array',
            'allow_waitlist' => 'boolean',
            'start_date'    => 'nullable|date', // 2025-01-01T00:00:00.000Z  Thu Jan 30 2025 00:00:00 GMT+0330 (Iran Standard Time)
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'prerequisites' => 'nullable|array',
            'tags'          => 'nullable|array',
            'thumbnail_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        // If prerequisites is provided as a single string, convert it to an array
        if (isset($data['prerequisites']) && !is_array($data['prerequisites'])) {
            $data['prerequisites'] = [$data['prerequisites']];
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

    public function getEnrolledCourses(Request $request)
    {
        // Assuming CourseEnrollment model has a relation 'course' that returns the course details.
        $enrollments = CourseEnrollment::with('course')
            ->where('user_id', auth()->id())
            ->get();

        // Map enrollments to extract course details
        // $courses = $enrollments->map(function ($enrollment) {
        //     return $enrollment->course;
        // });

        // Map each enrollment to include its status and a flag to allow redirection only if enrolled.
        $courses = $enrollments->map(function ($enrollment) {
            $course = $enrollment->course;
            if (!$course) {
                return null;
            }
            // Attach enrollment status and a redirection flag to the course object.
            $course->enrollment_status = $enrollment->status ?? 'pending';
            $course->can_redirect = ($course->enrollment_status === 'enrolled');

            return $course;
        })->filter()->values(); // Remove any null entries

        return response()->json([
            'courses' => $courses,
            'total' => $courses->count()
        ]);
    }

    // public function getCourseById($id)
    // {
    //     // Check that the authenticated student is enrolled in this course
    //     $enrollment = CourseEnrollment::where('course_id', $id)
    //     ->where('user_id', auth()->id())
    //     ->where('status', 'enrolled')
    //     ->first();

    // if (!$enrollment) {
    //     return response()->json(['message' => 'You are not enrolled in this course.'], 403);
    // }

    // $course = Course::with(['instructor', 'category'])->find($id);
    // if (!$course) {
    //     return response()->json(['message' => 'Course not found'], 404);
    // }
    // return response()->json($course);
    // }

    public function getPrerequisites(): JsonResponse
    {
        // $prerequisites = Course::select('course_code', 'course_name')
        //     ->orderBy('course_code')
        //     ->get();
        // return response()->json($prerequisites, 200);

        $courses = Course::select('prerequisites')->get();
        $allPrerequisites = [];

        foreach ($courses as $course) {
            if (is_array($course->prerequisites)) {
                $allPrerequisites = array_merge($allPrerequisites, $course->prerequisites);
            }
        }
        // Remove duplicate names and sort alphabetically
        $uniquePrerequisites = array_values(array_unique($allPrerequisites));
        sort($uniquePrerequisites);

        return response()->json($uniquePrerequisites, 200);
    }
}
