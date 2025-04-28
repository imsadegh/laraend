<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;

class AssignmentSubmissionController extends Controller
{
    public function store(Request $request, $assignmentId)
    {
        if (!is_numeric($assignmentId)) {
            return response()->json(['message' => 'Invalid assignment ID'], 400);
        }

        $assignment = Assignment::find($assignmentId);
        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        $user = auth()->user();

        // Ensure the student is enrolled in the course (using 'user_id' column)
        $enrollment = CourseEnrollment::where('course_id', $assignment->course_id)
            ->where('user_id', $user->id)
            ->where('status', 'enrolled')
            ->first();
        if (!$enrollment) {
            return response()->json(['message' => 'Unauthorized. You are not enrolled in this course.'], 403);
        }

        // Validate submission input
        $validated = $request->validate([
            'file' => 'nullable|file', // adjust file rules as needed
            'comments' => 'nullable|string',
        ]);

        // Store file if provided
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('assignment_submissions');
        }

        // Check if a submission already exists for this assignment and user
        $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingSubmission) {
            if ($existingSubmission->feedback || $existingSubmission->score !== null) {
                return response()->json([
                    'message' => 'This assignment has already been reviewed. Submission is locked.'
                ], 403);
            }

            // Update existing submission (increment revision number)
            $existingSubmission->update([
                'submission_date' => now(),
                'file_path' => $filePath ?? $existingSubmission->file_path,
                'comments' => $validated['comments'] ?? $existingSubmission->comments,
                'revision_number' => $existingSubmission->revision_number + 1,
            ]);
            $submission = $existingSubmission;
        } else {
            // Create new submission record
            $submission = AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
                'submission_date' => now(),
                'file_path' => $filePath,
                'comments' => $validated['comments'] ?? null,
                'revision_number' => 1, // initial submission
            ]);
        }

        return response()->json([
            'message' => 'Assignment submitted successfully.',
            'submission' => $submission,
        ], 201);
    }

    public function index()
    {
        $user = auth()->user();
        // Only allow instructors or admins
        if (!in_array($user->role->name, ['instructor', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Fetch submissions for assignments in courses taught by the instructor
        $submissions = AssignmentSubmission::with(['assignment', 'user', 'reviewedBy'])
            ->whereHas('assignment.course', function ($query) use ($user) {
                $query->where('instructor_id', $user->id);
            })
            ->get();

        // Map submissions to include desired fields
        $data = $submissions->map(function ($submission) {
            return [
                'id' => $submission->id,
                'assignment_title' => $submission->assignment ? $submission->assignment->title : null,
                'student_name' => $submission->user ? $submission->user->full_name : null,
                'submission_date' => $submission->submission_date,
                // 'file_path' => $submission->file_path,
                'comments' => $submission->comments,
                'score' => $submission->score,
                'revision_number' => $submission->revision_number,
                'is_late' => $submission->is_late,
                'feedback' => $submission->feedback,
                'last_reviewed_at' => $submission->last_reviewed_at,
                // 'reviewed_by' => $submission->reviewed_by,
                'reviewed_by' => optional($submission->reviewedBy)->full_name,
                'grade_visibility' => $submission->grade_visibility,
            ];
        });

        return response()->json($data);
    }

    public function review(Request $request, $id)
    {
        $validated = $request->validate([
            'score' => 'nullable|numeric|min:0|max:20',
            'feedback' => 'nullable|string',
            'grade_visibility' => 'nullable|boolean',
            'reviewed_by' => 'required|exists:users,id',
        ]);

        $submission = AssignmentSubmission::with('assignment.course')->find($id);
        if (!$submission) {
            return response()->json(['message' => 'Submission not found'], 404);
        }

        $instructorId = $submission->assignment->course->instructor_id;
        $user = auth()->user();
        if ($user->id !== $instructorId && $user->role->name !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $submission->update(array_merge(
            $validated,
            ['last_reviewed_at' => now()]
        ));

        return response()->json([
            'message' => 'Submission reviewed successfully',
            'submission' => $submission,
        ]);
    }
}
