<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;

class ExamAttemptAnswerController extends Controller
{
    public function store(Request $request, $attemptId)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.selected_option' => 'nullable|string',
            'answers.*.is_correct' => 'boolean',
            'answers.*.score_earned' => 'nullable|numeric',
        ]);

        $attempt = ExamAttempt::findOrFail($attemptId);
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // wipe out any old answers
        ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)->delete();

        foreach ($request->input('answers') as $ans) {
            ExamAttemptAnswer::create([
                'exam_attempt_id' => $attempt->id,
                'user_id' => $user->id,
                'question_id' => $ans['question_id'],
                'answer_text' => $ans['answer_text'] ?? null,
                'selected_option' => $ans['selected_option'] ?? null,
                'is_correct' => $ans['is_correct'] ?? false,
                'score_earned' => $ans['score_earned'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Answers saved'], 201);
    }




}
