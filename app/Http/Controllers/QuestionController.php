<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    private function ensureAdminOrInstructor()
    {
        $role = auth()->user()->role->name;
        if (!in_array($role, ['admin', 'instructor'])) {
            abort(403, 'Unauthorized');
        }
    }

    // List all questions
    public function index()
    {
        $this->ensureAdminOrInstructor();
        return response()->json(Question::all());
    }

    // Create a new question
    public function store(Request $request)
    {
        $this->ensureAdminOrInstructor();
        $v = $request->validate([
            'title' => 'nullable|string|max:255',
            'question_text' => 'required|string',
            'type' => 'required|in:multiple_choice,short_answer,true_false,essay',
            'options' => 'nullable|array',
            'options.*' => 'string',
            'correct_answers' => 'nullable|array',
            'correct_answers.*' => 'string',
        ]);
        $v['created_by'] = auth()->id();

        $question = Question::create($v);

        return response()->json([
            'message' => 'Question created',
            'question' => $question,
        ], 201);
    }

    // Get a single question
    public function show($id)
    {
        $this->ensureAdminOrInstructor();
        $q = Question::findOrFail($id);
        return response()->json($q);
    }

    // Update a question
    public function update(Request $request, $id)
    {
        $this->ensureAdminOrInstructor();
        $q = Question::findOrFail($id);

        // todo - merge these two:
        $v = $request->validate([
            'title' => 'nullable|string|max:255',
            'question_text' => 'required|string',
            'type' => 'required|in:multiple_choice,short_answer,true_false,essay',
            'options' => 'nullable|array',
            'options.*' => 'string',
            'correct_answers' => 'nullable|array',
            'correct_answers.*' => 'string',
        ]);
        // $v['created_by'] = auth()->id();

        $q->update($v);
        return response()->json(['message' => 'Question updated', 'question' => $q]);
    }

    // Soft‑delete a question
    public function destroy($id)
    {
        $this->ensureAdminOrInstructor();
        $q = Question::findOrFail($id);
        $q->delete();
        return response()->json(['message' => 'Question deleted']);
    }
}
