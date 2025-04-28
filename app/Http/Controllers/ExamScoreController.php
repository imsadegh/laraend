<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamScore;
use App\Models\ExamAttempt;

class ExamScoreController extends Controller
{
    public function store(Request $request)
    {
        $u = auth()->user();

        $data = $request->validate([
            'attempt_id' => 'required|exists:exam_attempts,id',
            'score' => 'nullable|numeric|min:0|max:20',
            'final_grade' => 'nullable|numeric|min:0|max:20',
            'grading_feedback' => 'nullable|array',
            'is_finalized' => 'boolean',
            'score_details' => 'nullable|array',
        ]);

        $attempt = ExamAttempt::with(['exam.course'])->findOrFail($data['attempt_id']);

        // only course instructor or admin can grade
        if ($u->role->name !== 'admin' && $attempt->exam->course->instructor_id !== $u->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // create or update existing score row
        $score = ExamScore::updateOrCreate(
            ['exam_id' => $attempt->exam_id, 'user_id' => $attempt->user_id, 'is_reexam' => false],
            array_merge($data, [
                'reviewed_by' => $u->id,
                'last_reviewed_at' => now(),
                'is_graded_automatically' => false,
            ])
        );

        return response()->json(['message' => 'Score saved', 'score' => $score->fresh()], 201);
    }

    public function index(Request $request)
    {
        $u = auth()->user();
        $now = now();                       // in case you add date filters later

        /* ── base query with eager-loads ───────────────────────── */
        $q = ExamScore::with([
            'exam.course:id,course_name,instructor_id',
            'user:id,first_name,last_name',
            'reviewer:id,first_name,last_name',
        ])->orderByDesc('created_at');

        /* ── role-based scoping ──────────────────────────────── */
        if ($u->role->name === 'student') {
            $q->where('user_id', $u->id);

            // optional: hide non-finalized scores from students
            // $q->where('is_finalized', true);
        } elseif ($u->role->name === 'instructor') {
            $q->whereHas('exam.course', fn($c) => $c->where('instructor_id', $u->id));
        }
        // admin sees everything (no extra where clause)

        /* ── optional filters ─────────────────────────────────── */
        if ($request->filled('course_id')) {
            $q->whereHas('exam', fn($e) => $e->where('course_id', $request->course_id));
        }
        if ($request->filled('exam_id')) {
            $q->where('exam_id', $request->exam_id);
        }

        $scores = $q->get();

        /* ── response mapping  (unchanged keys + extras) ─────── */
        $data = $scores->map(function ($s) {
            return [
                'id' => $s->id,
                'exam_title' => $s->exam?->name,
                'course_name' => $s->exam?->course?->course_name,
                'student_name' => $s->user?->full_name,
                'score' => $s->score,
                'final_grade' => $s->final_grade,
                'is_passed' => $s->is_passed,
                'is_finalized' => $s->is_finalized,
                'is_graded_automatically' => $s->is_graded_automatically,
                'last_reviewed_at' => $s->last_reviewed_at,
                'score_details' => $s->score_details,
                'grading_feedback' => $s->grading_feedback,
                'is_reexam' => $s->is_reexam,
                'reviewed_by' => $s->reviewer?->full_name,
            ];
        });

        return response()->json($data);
    }

    // patch / finalize
    public function update(Request $req, ExamScore $examScore)
    {
        $u = auth()->user();
        abort_if($u->role->name !== 'instructor' && $u->role->name !== 'admin', 403);
        abort_if($examScore->exam->course->instructor_id !== $u->id && $u->role->name !== 'admin', 403);

        $data = $req->validate([
            'final_grade' => 'nullable|numeric|min:0|max:20',
            'grading_feedback' => 'nullable|array',
            'is_finalized' => 'sometimes|boolean',
            'score_details' => 'nullable|array',
            'is_passed' => 'sometimes|boolean',
            'is_reexam' => 'sometimes|boolean',
        ]);

        $examScore->update(array_merge($data, [
            'reviewed_by' => $u->id,
            'last_reviewed_at' => now(),
        ]));

        return response()->json(['message' => 'Score updated', 'score' => $examScore->fresh()]);
    }

}
