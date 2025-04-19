<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;

class ExamQuestionController extends Controller
{
    // List questions for an exam
    // note: it must be student's exam if student are fetching and exam should active
    // should not fetch soft deleted questions
    // note: if the exam is not active, it should return 403
    public function index($examId)
    {
        $exam = Exam::findOrFail($examId);
        return response()->json($exam->questions); // assumes relation exam->questions()
    }

    // Attach a question to an exam
    // note: position and exam_id should be unique together
    // note: if the question is already attached to the exam, it should return 409
    // created by
    public function store(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);

        // ensure only the course's instructor (or admin) can do this
        $user = auth()->user();
        if ($user->role->name !== 'admin' && $exam->course->instructor_id !== $user->id) {
            return response()->json(['message'=>'Unauthorized'], 403);
        }

        $v = $request->validate([
            'question_id'  => 'required|exists:questions,id',
            'position'     => 'nullable|integer|min:0',
            'is_required'  => 'nullable|boolean',
        ]);

        $eq = ExamQuestion::create([
            'exam_id'     => $exam->id,
            'question_id' => $v['question_id'],
            'position'    => $v['position']    ?? null,
            'is_required' => $v['is_required'] ?? true,
        ]);

        return response()->json([
            'message'      => 'Question attached to exam',
            'exam_question'=> $eq,
        ], 201);
    }

    // Show a single exam‑question pivot
    public function show($examId, $id)
    {
        $eq = ExamQuestion::where('exam_id',$examId)->findOrFail($id);
        return response()->json($eq);
    }

    // Update the pivot (reorder / make optional)
    public function update(Request $request, $examId, $id)
    {
        $eq = ExamQuestion::where('exam_id',$examId)->findOrFail($id);
        $user = auth()->user();
        if ($user->role->name !== 'admin' && $eq->exam->course->instructor_id !== $user->id) {
            return response()->json(['message'=>'Unauthorized'], 403);
        }

        $v = $request->validate([
            'position'    => 'nullable|integer|min:0',
            'is_required' => 'nullable|boolean',
        ]);
        $eq->update($v);
        return response()->json(['message'=>'ExamQuestion updated','exam_question'=>$eq]);
    }

    // Detach (soft‑delete) a question from an exam
    public function destroy($examId, $id)
    {
        $eq = ExamQuestion::where('exam_id',$examId)->findOrFail($id);
        $user = auth()->user();
        if ($user->role->name !== 'admin' && $eq->exam->course->instructor_id !== $user->id) {
            return response()->json(['message'=>'Unauthorized'], 403);
        }
        $eq->delete();
        return response()->json(['message'=>'Question removed from exam']);
    }
}
