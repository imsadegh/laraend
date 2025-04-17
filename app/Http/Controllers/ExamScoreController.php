<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExamScoreController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role->name === 'student') {
            $scores = \App\Models\ExamScore::with(['exam', 'user', 'reviewer'])
                ->where('user_id', $user->id)
                ->get();
        } elseif ($user->role->name === 'instructor') {
            $scores = \App\Models\ExamScore::with(['exam', 'user', 'reviewer'])
                ->whereHas('exam.course', function ($query) use ($user) {
                    $query->where('instructor_id', $user->id);
                })
                ->get();
        } else {
            $scores = \App\Models\ExamScore::with(['exam', 'user', 'reviewer'])->get();
        }

        $data = $scores->map(function ($score) {
            return [
                'id' => $score->id,
                'exam_title' => $score->exam ? $score->exam->name : null,
                'student_name' => $score->user ? $score->user->full_name : null,
                'score' => $score->score,
                'final_grade' => $score->final_grade,
                'is_passed' => $score->is_passed,
                'is_finalized' => $score->is_finalized,
                'is_graded_automatically' => $score->is_graded_automatically,
                'last_reviewed_at' => $score->last_reviewed_at,
                'score_details' => $score->score_details,
                'grading_feedback' => $score->grading_feedback,
                'is_reexam' => $score->is_reexam,
                'reviewed_by' => optional($score->reviewer)->full_name,
            ];
        });

        return response()->json($data);
    }
}
