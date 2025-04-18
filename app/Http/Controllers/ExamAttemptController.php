<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;

class ExamAttemptController extends Controller
{
    public function store(Request $request, $examId)
    {
        if (!is_numeric($examId)) {
            return response()->json(['message' => 'Invalid exam ID'], 400);
        }

        $exam = Exam::find($examId);
        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        $user = auth()->user();

        // Ensure the exam is linked to a course
        if (!$exam->course) {
            return response()->json(['message' => 'Exam course not found'], 404);
        }

        // Ensure that the student is enrolled in the exam's course
        $enrollment = \App\Models\CourseEnrollment::where('course_id', $exam->course_id)
            ->where('user_id', $user->id)
            ->where('status', 'enrolled')
            ->first();
        if (!$enrollment) {
            return response()->json(['message' => 'Unauthorized. You are not enrolled in this course.'], 403);
        }

        // Determine the next attempt number for this exam and student
        $lastAttempt = ExamAttempt::where('exam_id', $examId)
            ->where('user_id', $user->id)
            ->orderBy('attempt_number', 'desc')
            ->first();
        $newAttemptNumber = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

        // Create new exam attempt
        $attempt = ExamAttempt::create([
            'exam_id' => $examId,
            'user_id' => $user->id,
            'attempt_number' => $newAttemptNumber,
            'started_at' => now(),
            'is_submitted' => false,
        ]);

        return response()->json([
            'message' => 'Exam attempt started successfully.',
            'attempt' => $attempt,
        ], 201);
    }

    public function update(Request $request, $attemptId)
    {
        $attempt = ExamAttempt::find($attemptId);
        if (!$attempt) {
            return response()->json(['message' => 'Exam attempt not found'], 404);
        }

        $user = auth()->user();

        // Only the owner can submit their attempt
        if ($attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only expect an “is_submitted” flag (and optional finished_at override)
        $validated = $request->validate([
            'is_submitted' => 'required|boolean',
            'finished_at' => 'nullable|date',
        ]);

        // If student is submitting, record finish time (or use now())
        // if ($validated['is_submitted']) {
            $attempt->update([
                'is_submitted' => $validated['is_submitted'] ?? true,
                'finished_at' => $validated['finished_at'] ?? now(),
            ]);
        // }

        return response()->json([
            'message' => 'Exam attempt updated successfully.',
            'attempt' => $attempt->fresh(),
        ]);
    }

    public function show($attemptId)
    {
        $attempt = ExamAttempt::with(['exam', 'user', 'answers'])->find($attemptId);
        if (!$attempt) {
            return response()->json(['message' => 'Exam attempt not found'], 404);
        }

        $user = auth()->user();

        // If the user is a student, they can only view their own exam attempt.
        if ($user->role->name === 'student' && $attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // If the user is an instructor, ensure they are the instructor for the course that owns this exam.
        if ($user->role->name === 'instructor') {
            if (!$attempt->exam || !$attempt->exam->course || $attempt->exam->course->instructor_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json($attempt);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role->name, ['instructor', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attempts = ExamAttempt::with(['exam', 'user', 'exam.course'])
            ->whereHas('exam.course', function ($query) use ($user) {
                $query->where('instructor_id', $user->id);
            })
            ->get();

        return response()->json($attempts);
    }

    public function review(Request $request, $attemptId)
    {
        $validated = $request->validate([
            'score' => 'nullable|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
            'grade_visibility' => 'nullable|boolean',
            'reviewed_by' => 'required|exists:users,id',
        ]);

        $attempt = ExamAttempt::with('exam.course')->find($attemptId);
        if (!$attempt) {
            return response()->json(['message' => 'Exam attempt not found'], 404);
        }

        $user = auth()->user();
        if ($user->id !== $attempt->exam->course->instructor_id && $user->role->name !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attempt->update(array_merge(
            $validated,
            ['last_reviewed_at' => now()]
        ));

        return response()->json([
            'message' => 'Exam attempt reviewed successfully',
            'attempt' => $attempt,
        ]);
    }


}
