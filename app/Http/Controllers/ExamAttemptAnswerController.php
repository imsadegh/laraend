<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\ExamScore;

class ExamAttemptAnswerController extends Controller
{
    public function store(Request $request, $attemptId)
    {
        // validate request
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

        // authorisation
        $attempt = ExamAttempt::findOrFail($attemptId);
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // single-answer path
        if ($single) {
            $data = $request->only(['question_id', 'answer_text', 'selected_option']);

            // avoid duplicate
            $dup = ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)
                ->where('question_id', $data['question_id'])->exists();
            if ($dup)
                return response()->json(['message' => 'Already answered'], 422);

            // ***
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

                /* simple MCQ/TF scoring */
                $earned = 0;
                $detail = [];
                foreach ($attempt->answers()->with('question')->get() as $ans) {
                    $q = $ans->question;
                    $ok = false;

                    if (in_array($q->type, ['multiple_choice', 'true_false'])) {
                        $ok = collect($q->correct_answers)->contains(
                            $q->type === 'true_false'
                            ? filter_var($ans->selected_option, FILTER_VALIDATE_BOOLEAN)
                            : $ans->selected_option
                        );
                    }
                    $earned += $ok ? 1 : 0;
                    $detail[] = ['question_id' => $q->id, 'earned' => $ok ? 1 : 0];
                }
                $pct = round(($earned / max(1, $totalQs)) * 100, 2);

                /* ← HERE is the critical change: updateOrCreate on attempt_id */
                ExamScore::updateOrCreate(
                    ['attempt_id' => $attempt->id],                 // unique key
                    [
                        'exam_id' => $attempt->exam_id,
                        'user_id' => $attempt->user_id,
                        'score' => $pct,
                        'is_passed' => $pct >= 50,
                        'is_finalized' => false,
                        'is_graded_automatically' => true,
                        'final_grade' => null,
                        'score_details' => $detail,
                    ]
                );
            }

            return response()->json(['message' => 'Answer saved'], 201);

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


            return response()->json(['message' => 'Answers saved'], 201);
        }
    }




}
