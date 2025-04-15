<?php

namespace App\Http\Controllers;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    // Get all assignments for a course
    public function index()
    {
        // method 1:
        // $course = Course::find($courseId);
        // if (!$course) {
        //     return response()->json(['message' => 'Course not found'], 404);
        // }

        // $assignments = Assignment::where('course_id', $courseId)->get();
        // return response()->json($assignments);

        // method 2:
        // $user = auth()->user();
        // // Get all courses where the user is the instructor
        // $courses = Course::where('instructor_id', $user->id)->get();

        // // Fetch all assignments for these courses
        // $assignments = Assignment::whereIn('course_id', $courses->pluck('id'))->get();

        // return response()->json($assignments);

        // method 3:
        $assignments = Assignment::with('course') // Assuming `course` is the relation on the Assignment model
            ->whereHas('course', function ($query) {
                $query->where('instructor_id', auth()->id());
            })
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'submission_deadline' => $assignment->submission_deadline,
                    'max_score' => $assignment->max_score,
                    'course_name' => $assignment->course->course_name, // Add course name
                    'visible' => $assignment->visible,
                    'type' => $assignment->type,
                ];
            });

        return response()->json($assignments);
    }

    // Get a single assignment
    public function show($id)
    {
        $assignment = Assignment::find($id);
        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        // For student users, return assignment details along with their submission (if any)
        if (auth()->user()->role->name === 'student') {
            $submission = AssignmentSubmission::where('assignment_id', $id)
                ->where('user_id', auth()->id())
                ->first();
            return response()->json([
                'assignment' => $assignment,
                'submission' => $submission
            ]);
        }

        // For other roles, return the full assignment details
        return response()->json($assignment);
    }

    // Create a new assignment
    public function store(Request $request, Course $course)
    {
        // $course = Course::find($courseId);
        // if (!$course) {
        //     return response()->json(['message' => 'Course not found'], 404);
        // }

        // if ($course->instructor_id != auth()->id() && auth()->user()->role->name !== 'admin')
        // Ensure the authenticated user is the instructor for this course
        if (!auth()->check() || (($course->instructor_id != auth()->user()->id || !auth()->user()->is_verified) && auth()->user()->role->name !== 'admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'submission_deadline' => 'nullable|date',
            'requirements' => 'nullable|json',
            'max_score' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'type' => 'required|in:individual,group',
            'allow_late_submission' => 'boolean',
            'visible' => 'required|boolean',
            'late_submission_penalty' => 'nullable|integer|min:0|max:100',
            'resources' => 'nullable|json',
            'revision_limit' => 'integer|min:1',
            'published_at' => 'nullable|date',
            'last_submission_at' => 'nullable|date|after_or_equal:submission_deadline',
        ]);

        // $assignment = Assignment::create([
        //     'course_id' => $courseId,
        //     'title' => $validated['title'],
        //     'description' => $validated['description'] ?? null,
        //     'submission_deadline' => $validated['submission_deadline'] ?? null,
        //     'requirements' => $validated['requirements'] ?? null,
        //     'max_score' => $validated['max_score'],
        //     'is_active' => $validated['is_active'] ?? true,
        //     'type' => $validated['type'],
        //     'allow_late_submission' => $validated['allow_late_submission'] ?? false,
        //     'visible' => $validated['visible'] ?? false,  // Default to false if not specified
        //     'late_submission_penalty' => $validated['late_submission_penalty'] ?? null,
        //     'resources' => $validated['resources'] ?? null,
        //     'revision_limit' => $validated['revision_limit'],
        //     'published_at' => $validated['published_at'] ?? null,
        //     'last_submission_at' => $validated['last_submission_at'] ?? null,
        // ]);

        // Add a new field indicating which course this assignment is for.
        $validated['for_course'] = $course->id;

        $assignment = Assignment::create(array_merge($validated, [
            'course_id' => $course->id,
        ]));


        return response()->json([
            'message' => 'Assignment created successfully',
            'assignments' => $assignment,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $assignment = Assignment::find($id);
        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        // Ensure the authenticated user is the instructor for the course associated with the assignment
        $course = $assignment->course;
        if (!auth()->check() || (($course->instructor_id != auth()->user()->id || !auth()->user()->is_verified) && auth()->user()->role->name !== 'admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assignment->update($request->all());
        return response()->json(['message' => 'Assignment updated successfully', 'assignment' => $assignment], 200);
    }

    public function destroy($id)
    {
        $assignment = Assignment::find($id);
        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        // Ensure the authenticated user is the instructor for the course associated with the assignment
        $course = $assignment->course;
        if (!auth()->check() || (($course->instructor_id != auth()->user()->id || !auth()->user()->is_verified) && auth()->user()->role->name !== 'admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assignment->delete();
        return response()->json(['message' => 'Assignment deleted successfully'], 200);
    }

    public function getCourseAssignments($courseId)
    {
        // Find the course by ID
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // Ensure the authenticated student is enrolled in this course with status "enrolled"
        $enrollment = \App\Models\CourseEnrollment::where('course_id', $courseId)
            ->where('user_id', auth()->id())
            ->where('status', 'enrolled')
            ->first();
        if (!$enrollment) {
            return response()->json(['message' => 'Unauthorized. You are not enrolled in this course.'], 403);
        }

        // Fetch assignments for the course
        $assignments = Assignment::where('course_id', $courseId)
            ->where('visible', true)
            ->get();

        // Optionally, return only a subset of fields:
        $assignments = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'submission_deadline' => $assignment->submission_deadline,
                'max_score' => $assignment->max_score,
                'visible' => $assignment->visible,
                'type' => $assignment->type,
                'is_active' => $assignment->is_active,
            ];
        });

        return response()->json($assignments);
    }

}
