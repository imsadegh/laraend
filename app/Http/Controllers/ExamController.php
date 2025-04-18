<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role->name === 'admin') {
            // Admin: return all exams with course details.
            $exams = Exam::with('course')->get();
        } elseif ($user->role->name === 'instructor') {
            // Instructor: return exams for courses they teach.
            $exams = Exam::with('course')->whereHas('course', function ($query) use ($user) {
                $query->where('instructor_id', $user->id);
            })->get();
        } else {
            // Student: return exams for courses where the student is enrolled.
            $exams = Exam::with('course')
                ->whereHas('course.enrollments', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('status', 'enrolled');
                })->get();
        }

        return response()->json($exams);
    }


    public function store(Request $request)
    {
        $user = auth()->user();

        // 1. Validate input
        $data = $request->validate([
            'course_id'        => 'required|exists:courses,id',
            'name'             => 'required|string|max:255',
            'intro'            => 'nullable|string',
            'time_open'        => 'nullable|date',
            'time_close'       => 'nullable|date|after:time_open',
            'time_limit'       => 'nullable|integer|min:1',
            'grade'            => 'required|integer|min:0',
            'questions_count'  => 'required|integer|min:1',
            'exam_type'        => 'required|in:multiple_choice,short_answer,true_false,essay',
            'shuffle_questions'=> 'nullable|boolean',
            'shuffle_answers'  => 'nullable|boolean',
            'attempts'         => 'required|integer|min:1',
            'feedback_enabled' => 'nullable|boolean',
            'version'          => 'nullable|integer|min:1',
            'question_pool'    => 'nullable|array',
            'status'           => 'required|in:active,archived,draft',
        ]);

        // 2. Authorization: only admin or the course's instructor
        $course = Course::find($data['course_id']);
        if (! $course) {
            return response()->json(['message' => 'Course not found'], 404);
        }
        if ($user->role->name !== 'admin' && $course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 3. Build & save
        $exam = new Exam();
        $exam->course_id         = $data['course_id'];
        $exam->name              = $data['name'];
        $exam->intro             = $data['intro'] ?? null;
        $exam->time_open         = $data['time_open'] ?? null;
        $exam->time_close        = $data['time_close'] ?? null;
        $exam->time_limit        = $data['time_limit'] ?? null;
        $exam->grade             = $data['grade'];
        $exam->questions_count   = $data['questions_count'];
        $exam->exam_type         = $data['exam_type'];
        $exam->shuffle_questions = $request->boolean('shuffle_questions', true);
        $exam->shuffle_answers   = $request->boolean('shuffle_answers', true);
        $exam->attempts          = $data['attempts'];
        $exam->feedback_enabled  = $request->boolean('feedback_enabled', true);
        $exam->version           = $data['version'] ?? 1;
        $exam->question_pool     = $data['question_pool'] ?? [];
        $exam->status            = $data['status'];
        $exam->created_by        = $user->id;
        $exam->save();

        return response()->json([
            'message' => 'Exam created successfully',
            'exam'    => $exam,
        ], 201);
    }

    public function show($id)
    {
        $exam = Exam::with('course')->find($id);
        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        $user = auth()->user();

        // If user is admin or the instructor for the course owning the exam, return full exam details.
        if ($user->role->name === 'admin' || ($exam->course && $exam->course->instructor_id == $user->id)) {
            return response()->json($exam);
        }

        // Otherwise assume the user is a student; check that they are enrolled in the exam's course.
        $enrollment = \App\Models\CourseEnrollment::where('course_id', $exam->course_id)
            ->where('user_id', $user->id)
            ->where('status', 'enrolled')
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Unauthorized. You are not enrolled in this course.'], 403);
        }

        return response()->json($exam);
    }

    public function update(Request $request, $id)
    {
        $exam = Exam::with('course')->find($id);
        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        $user = auth()->user();
        if ($user->role->name !== 'admin' && (!$exam->course || $exam->course->instructor_id != $user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'course_id' => 'sometimes|exists:courses,id',
            'name' => 'sometimes|required|string|max:255',
            'intro' => 'nullable|string',
            'time_open' => 'nullable|date',
            'time_close' => 'nullable|date|after:time_open',
            'time_limit' => 'nullable|integer|min:1',
            'grade' => 'sometimes|required|integer|min:0',
            'exam_type' => 'sometimes|required|in:multiple_choice,short_answer,true_false,essay',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_answers' => 'nullable|boolean',
            'attempts' => 'sometimes|required|integer|min:1',
            'feedback_enabled' => 'nullable|boolean',
            'version' => 'nullable|integer|min:1',
            'question_pool' => 'nullable|json',
            'status' => 'sometimes|required|in:active,archived,draft',
            // 'time_zone' => 'sometimes|required|string',
        ]);

        if (isset($validated['course_id'])) {
            $course = Course::find($validated['course_id']);
            if (!$course || $course->instructor_id != $user->id) {
                return response()->json(['message' => 'Unauthorized: Invalid course'], 403);
            }
        }

        $exam->update($validated);

        return response()->json([
            'message' => 'Exam updated successfully',
            'exam' => $exam,
        ]);
    }

    public function destroy($id)
    {
        $exam = Exam::with('course')->find($id);
        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        $user = auth()->user();
        if ($user->role->name !== 'admin' && (!$exam->course || $exam->course->instructor_id != $user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $exam->delete(); // Soft delete the exam

        return response()->json(['message' => 'Exam deleted successfully'], 200);
    }

}
