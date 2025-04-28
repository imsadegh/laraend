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

        // $exam = Exam::find($examId);
        // if (!$exam) {
        //     return response()->json(['message' => 'Exam not found'], 404);
        // }
        $exam = Exam::with('questions')->findOrFail($examId);

        // exam must be published, active and within time window
        $now = now();
        if (
            !$exam->is_published || $exam->status !== 'active'
            || ($exam->time_open && $now->lt($exam->time_open))
            || ($exam->time_close && $now->gt($exam->time_close))
        ) {
            return response()->json(['message' => 'Exam not available'], 403);
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

        // // Determine the next attempt number for this exam and student
        // $lastAttempt = ExamAttempt::where('exam_id', $examId)
        //     ->where('user_id', $user->id)
        //     ->orderBy('attempt_number', 'desc')
        //     ->first();
        // $newAttemptNumber = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

        // // Create new exam attempt
        // $attempt = ExamAttempt::create([
        //     'exam_id' => $examId,
        //     'user_id' => $user->id,
        //     'attempt_number' => $newAttemptNumber,
        //     'started_at' => now(),
        //     'is_submitted' => false,
        // ]);

        // resume unfinished attempt if any
        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('user_id', $user->id)
            ->whereNull('finished_at')
            ->first();

        if (!$attempt) {
            // ✦ ENFORCE MAX ATTEMPTS
            $max = $exam->attempts ?? 1;                    // ← column “attempts” in exams table
            $taken = ExamAttempt::where('exam_id', $examId)
                ->where('user_id', $user->id)
                ->whereNotNull('finished_at')
                ->count();

            if ($taken >= $max) {
                return response()->json(['message' => 'Maximum attempts reached'], 403);
            }

            // next attempt number
            $nextNum = ExamAttempt::where('exam_id', $examId)
                ->where('user_id', $user->id)
                ->max('attempt_number') + 1;

            $attempt = ExamAttempt::create([
                'exam_id' => $examId,
                'user_id' => $user->id,
                'attempt_number' => $nextNum ?: 1,
                'started_at' => now(),
            ]);
        }

        // return response()->json([
        //     'message' => 'Exam attempt started successfully.',
        //     'attempt' => $attempt,
        // ], 201);
        // first unanswered question
        $first = $this->firstUnanswered($attempt);

        return response()->json([
            'attempt' => $attempt,
            'question' => $first,
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
        // $attempt = ExamAttempt::with(['exam', 'user', 'answers'])->find($attemptId);

        // eager-load: course → security check, answers.question → review screen, examScore (if any)
        $attempt = ExamAttempt::with([
            'exam.course',
            // ↓  limit columns sent to frontend
            'user:id,full_name',
            'answers.question',
            'examScore',          // add hasOne() in model if missing
        ])->find($attemptId);

        if (!$attempt) {
            return response()->json(['message' => 'Exam attempt not found'], 404);
        }

        $user = auth()->user();

        // If the user is a student, they can only view their own exam attempt.
        if ($user->role->name === 'student' && $attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // If the user is an instructor, ensure they are the instructor for the course that owns this exam.
        if ($user->role->name === 'instructor' && $attempt->exam->course->instructor_id !== $user->id) {
            // if (!$attempt->exam || !$attempt->exam->course || $attempt->exam->course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
            // }
        }

        return response()->json($attempt);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        /* ── role check ───────────────────────────── */
        if (!in_array($user->role->name, ['instructor', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        /* ── base query with eager-loads ──────────── */
        // $q = ExamAttempt::with(['exam.course', 'user'])
        $q = ExamAttempt::with(['exam.course', 'user', 'examScore'])
            ->orderByDesc('created_at');

        /* ── restrict to instructor’s own courses ── */
        if ($user->role->name === 'instructor') {
            $q->whereHas('exam.course', fn($c) => $c->where('instructor_id', $user->id));
        }

        /* ── optional filters ─────────────────────── */
        if ($request->filled('course_id')) {
            $q->whereHas('exam', fn($e) => $e->where('course_id', $request->course_id));
        }
        if ($request->filled('exam_id')) {
            $q->where('exam_id', $request->exam_id);
        }

        return response()->json($q->get());
    }


    public function review(Request $request, $attemptId)
    {
        $validated = $request->validate([
            'score' => 'nullable|numeric|min:0|max:20',
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

    // helpers
    private function firstUnanswered($attempt)
    {
        $answered = $attempt->answers()->pluck('question_id');

        return $attempt->exam->questions()
            // real column names on the pivot table
            ->orderBy('exam_questions.position')
            ->orderBy('exam_questions.id')
            ->whereNotIn('questions.id', $answered)
            ->first();
    }



    // -----------------------------------

    // ⓑ post answer (one question at a time)
    // public function answer(Request $req, Exam $exam, $attemptId)
    // {
    //     $attempt = $exam->attempts()->whereKey($attemptId)->firstOrFail();
    //     $this->abortIfClosed($attempt);

    //     $v = $req->validate([
    //         'question_id' => ['required', 'integer', Rule::exists('questions', 'id')],
    //         'response' => 'required',
    //     ]);

    //     // prevent duplicate answers
    //     $exists = $attempt->answers()
    //         ->where('question_id', $v['question_id'])
    //         ->exists();
    //     if ($exists)
    //         return response()->json(['message' => 'Already answered'], 422);

    //     $attempt->answers()->create($v);

    //     $next = $this->firstUnanswered($attempt);
    //     if (!$next) {
    //         $attempt->update(['finished_at' => now()]);
    //     }

    //     return response()->json(['next_question' => $next]);
    // }

    // ⓒ return next unanswered question (GET)
    public function next(Exam $exam, $attemptId)
    {
        $attempt = $exam->attempts()->whereKey($attemptId)->firstOrFail();
        $this->abortIfClosed($attempt);

        return response()->json(['question' => $this->firstUnanswered($attempt)]);
    }

    private function abortIfClosed($attempt)
    {
        if ($attempt->finished_at)
            abort(403, 'Attempt already finished');
    }

}
