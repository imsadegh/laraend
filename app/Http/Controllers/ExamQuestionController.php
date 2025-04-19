<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\CourseEnrollment;

class ExamQuestionController extends Controller
{
    // List questions for an exam
    public function index($examId)
    {
        $exam = Exam::with('course')->findOrFail($examId);
        $user = auth()->user();

        // 1. If student, must be enrolled in course
        if ($user->role->name === 'student') {
            // 2. Exam must be active
            if ($exam->status !== 'active') {
                return response()->json(['message' => 'This exam is not available'], 403);
            }

            $enrolled = CourseEnrollment::where('course_id', $exam->course_id)
                ->where('user_id', $user->id)
                ->where('status', 'enrolled')
                ->exists();

            if (!$enrolled) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        // 3. Fetch only non‑deleted exam questions
        $questions = $exam->questions()->whereNull('exam_questions.deleted_at')->get(); // Eloquent SoftDeletes will automatically exclude trashed rows

        return response()->json($questions);
    }

    // Attach a question to an exam
    public function store(Request $request, $examId)
    {
        $exam = Exam::with('course')->findOrFail($examId);

        // ensure only the course's instructor (or admin) can do this
        $user = auth()->user();
        if ($user->role->name !== 'admin' && $exam->course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $rules = [
            'question_id' => [
                'required',
                Rule::exists('questions', 'id')->whereNull('deleted_at'),
                Rule::unique('exam_questions', 'question_id')
                    ->where('exam_id', $exam->id),
            ],
            'position' => [
                'nullable',
                'integer',
                'min:0',
                Rule::unique('exam_questions', 'position')
                    ->where('exam_id', $exam->id),
            ],
            'is_required' => 'nullable|boolean',
        ];

        $messages = [
            'question_id.unique' => 'That question is already attached to this exam',
            'position.unique' => 'That position is already in use for this exam',
        ];

        $v = $request->validate($rules, $messages);

        $eq = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_id' => $v['question_id'],
            'position' => $v['position'] ?? null,
            'is_required' => $v['is_required'] ?? true,
            'created_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Question attached to exam',
            'exam_question' => $eq,
        ], 201);
    }

    // Show a single exam‑question pivot
    public function show($examId, $id)
    {
        $eq = ExamQuestion::where('exam_id', $examId)->findOrFail($id);
        return response()->json($eq);
    }

    // Update the pivot (reorder / make optional)
    public function update(Request $request, $examId, $id)
    {
        $eq = ExamQuestion::where('exam_id', $examId)->findOrFail($id);
        $user = auth()->user();
        if ($user->role->name !== 'admin' && $eq->exam->course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // todo - merge these two:
        $v = $request->validate([
            'position' => 'nullable|integer|min:0',
            'is_required' => 'nullable|boolean',
            // 'created_by' => auth()->id()
        ]);
        $v['created_by'] = auth()->id();

        $eq->update($v);
        return response()->json(['message' => 'ExamQuestion updated', 'exam_question' => $eq]);
    }

    // Detach (soft‑delete) a question from an exam
    public function destroy($examId, $id)
    {
        $eq = ExamQuestion::where('exam_id', $examId)->findOrFail($id);
        $user = auth()->user();
        if ($user->role->name !== 'admin' && $eq->exam->course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $eq->delete();
        return response()->json(['message' => 'Question removed from exam']);
    }
}
