<?php

namespace App\Http\Controllers;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    // Get all assignments for a course
    public function index($courseId)
    {
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $assignments = Assignment::where('course_id', $courseId)->get();
        return response()->json($assignments);
    }

    // Get a single assignment
    public function show($id)
    {
        $assignment = Assignment::find($id);
        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }
        return response()->json($assignment);
    }

    // Create a new assignment
    public function store(Request $request, $courseId)
    {
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

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

        $assignment = Assignment::create([
            'course_id' => $courseId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'submission_deadline' => $validated['submission_deadline'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'max_score' => $validated['max_score'],
            'is_active' => $validated['is_active'] ?? true,
            'type' => $validated['type'],
            'allow_late_submission' => $validated['allow_late_submission'] ?? false,
            'visible' => $validated['visible'] ?? false,  // Default to false if not specified
            'late_submission_penalty' => $validated['late_submission_penalty'] ?? null,
            'resources' => $validated['resources'] ?? null,
            'revision_limit' => $validated['revision_limit'],
            'published_at' => $validated['published_at'] ?? null,
            'last_submission_at' => $validated['last_submission_at'] ?? null,
        ]);

        return response()->json(['message' => 'Assignment created successfully', 'assignments' => $assignment,], 201);
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
}
