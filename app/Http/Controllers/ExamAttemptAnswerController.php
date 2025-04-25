<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;

class ExamAttemptAnswerController extends Controller
{
    public function store(Request $request, $attemptId)
    {
        // $request->validate([
        //     'answers' => 'required|array',
        //     'answers.*.question_id' => 'required|exists:questions,id',
        //     'answers.*.answer_text' => 'nullable|string',
        //     'answers.*.selected_option' => 'nullable|string',
        //     'answers.*.is_correct' => 'boolean',
        //     'answers.*.score_earned' => 'nullable|numeric',
        // ]);
        $single = !$request->has('answers');

        $rules = $single
            ? [
                'question_id' => 'required|exists:questions,id',
                'answer_text' => 'nullable|string',
                'selected_option' => 'nullable|string',
            ]
            : [
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|exists:questions,id',
                'answers.*.answer_text' => 'nullable|string',
                'answers.*.selected_option' => 'nullable|string',
            ];
        $request->validate($rules);

        $attempt = ExamAttempt::findOrFail($attemptId);
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // // wipe out any old answers
        // ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)->delete();

        // foreach ($request->input('answers') as $ans) {
        //     ExamAttemptAnswer::create([
        //         'exam_attempt_id' => $attempt->id,
        //         'user_id' => $user->id,
        //         'question_id' => $ans['question_id'],
        //         'answer_text' => $ans['answer_text'] ?? null,
        //         'selected_option' => $ans['selected_option'] ?? null,
        //         'is_correct' => $ans['is_correct'] ?? false,
        //         'score_earned' => $ans['score_earned'] ?? null,
        //     ]);
        // }
// save
        if ($single) {
            $data = $request->only(['question_id', 'answer_text', 'selected_option']);

            // avoid duplicate
            $exists = ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)
                ->where('question_id', $data['question_id'])
                ->exists();
            if ($exists)
                return response()->json(['message' => 'Already answered'], 422);

            ExamAttemptAnswer::create(array_merge($data, [
                'exam_attempt_id' => $attempt->id,
                'user_id' => $user->id,
            ]));

            // ⟡ if that was the final unanswered question → finish attempt
            $totalQs = $attempt->exam->questions()->count();
            $answered = $attempt->answers()->count();
            if ($answered === $totalQs) {
                $attempt->update([
                    'finished_at' => now(),
                    'is_submitted' => true,
                ]);
            }
        } else {
            // wipe & bulk-insert
            ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)->delete();
            foreach ($request->input('answers') as $ans) {
                ExamAttemptAnswer::create([
                    'exam_attempt_id' => $attempt->id,
                    'user_id' => $user->id,
                    'question_id' => $ans['question_id'],
                    'answer_text' => $ans['answer_text'] ?? null,
                    'selected_option' => $ans['selected_option'] ?? null,
                ]);
            }


            return response()->json(['message' => 'Answer(s) saved'], 201);
        }
    }




}
