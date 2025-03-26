<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\CourseEnrollment;
use App\Models\TuitionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseEnrollmentController extends Controller
{
    // List enrollments for a given course
    /**
     * List enrollments for a course along with tuition summary for each student.
     */
    public function index(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $courseId = $request->course_id;

        // Retrieve enrollments with student details.
        // Also join with tuition_history table to calculate total amount paid per student.
        $enrollments = CourseEnrollment::with(['student', 'course'])
            ->where('course_id', $courseId)
            ->get()
            ->map(function ($enrollment) use ($courseId) {
                // Sum up tuition payments for the student in this course
                $totalPaid = TuitionHistory::where('course_id', $courseId)
                    ->where('user_id', $enrollment->user_id)
                    ->where('payment_status', 'paid')
                    ->sum('amount_paid');

                $enrollment->total_paid = $totalPaid;
                // Add course name and course fee (assuming "tuition_fee" column exists)
                $enrollment->course_name = $enrollment->course ? $enrollment->course->course_name : null;
                $enrollment->course_price = $enrollment->course ? $enrollment->course->tuition_fee : null;


                return $enrollment;
            });

        return response()->json($enrollments);
    }


    // Update enrollment status and optionally the amount paid
    /**
     * Update the enrollment status.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:enrolled,waitlisted,completed,dropped,pending',
            // You can add other fields that you want to update, e.g., 'active'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $enrollment = CourseEnrollment::find($id);
        if (!$enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }

        $enrollment->update($request->only(['status', 'active', 'eligible_for_enrollment']));
        return response()->json([
            'message' => 'Enrollment updated successfully',
            'enrollment' => $enrollment,
        ], 200);
    }


    public function enrollStudent(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'course_id'  => 'required|exists:courses,id',
            'student_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check that the authenticated user is an admin.
        // If you have a role property or helper method, you can do:
        if (auth()->user()->role->name !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $course = Course::find($request->course_id);
        $student = User::find($request->student_id);

        // Check if the student is already enrolled
        $existingEnrollment = CourseEnrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();
        if ($existingEnrollment) {
            return response()->json(['message' => 'Student is already enrolled in this course.'], 409);
        }

        // Create the enrollment record
        $enrollment = CourseEnrollment::create([
            'course_id'  => $course->id,
            'student_id' => $student->id,
        ]);

        // Optionally update the course's enrolled_students_count
        $course->increment('enrolled_students_count');

        return response()->json([
            'message'    => 'Student enrolled successfully.',
            'enrollment' => $enrollment,
        ], 201);
    }

}
