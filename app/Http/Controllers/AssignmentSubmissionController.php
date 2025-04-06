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
            // Update existing submission (increment revision number)
            $existingSubmission->update([
                'submission_date' => now(),
                'file_path'       => $filePath ?? $existingSubmission->file_path,
                'comments'        => $validated['comments'] ?? $existingSubmission->comments,
                'revision_number' => $existingSubmission->revision_number + 1,
            ]);
            $submission = $existingSubmission;
        } else {
            // Create new submission record
            $submission = AssignmentSubmission::create([
                'assignment_id'   => $assignment->id,
                'user_id'         => $user->id,
                'submission_date' => now(),
                'file_path'       => $filePath,
                'comments'        => $validated['comments'] ?? null,
                'revision_number' => 1, // initial submission
            ]);
        }

        return response()->json([
            'message'    => 'Assignment submitted successfully.',
            'submission' => $submission,
        ], 201);
    }

}
