<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TuitionHistory;
use App\Models\Course;

class TuitionController extends Controller
{
    public function pay(Request $request)
    {
        // 1. Validate request
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'sometimes|in:credit_card,bank_transfer,zarinpal,cash',
        ]);

        // 2. Simulate a payment gateway process
        $paymentStatus = $this->processPayment($validated['amount_paid'], $validated['payment_method'] ?? 'credit_card');

        // Save payment details in tuition_history table
        $tuition = TuitionHistory::create([
            'user_id' => auth()->id(),
            'course_id' => $validated['course_id'],
            'amount_paid' => $validated['amount_paid'],
            'payment_status' => $paymentStatus,
            'payment_date' => $paymentStatus === 'paid' ? now() : null,
            'payment_method' => $validated['payment_method'] ?? 'credit_card',
            'transaction_id' => uniqid('trx_'),
            'payment_details' => null, // You can store JSON from the gateway here
            'last_updated_status_at' => now(),
            'is_refundable' => false,
        ]);

        // 4. Enroll the student if the payment was successful
        if ($paymentStatus === 'paid') {
            // Retrieve the course
            $course = Course::find($validated['course_id']);
            // Check if not already enrolled
            if (!$course->students()->where('user_id', auth()->id())->exists()) {
                $course->students()->attach(auth()->id()); // this will create a new record in the course_enrollments pivot table
            }

            return response()->json([
                'message' => 'Payment successful. Student enrolled.',
                'paymentRecord' => $tuition,
            ], 200);
        }

        return response()->json([
            'message' => 'Payment failed or is pending.',
            'paymentRecord' => $tuition,
        ], 400);
    }

    /**
     * Fake payment gateway processing.
     *
     * @param  float  $amount
     * @param  string $method
     * @return string
     */
    private function processPayment(float $amount, string $method)
    {
        // This is where you'd integrate with Stripe, zarinpal, etc.
        // For now, let's simulate a success if amount >= 1
        if ($amount >= 10) {
            return 'paid';   // or 'pending'
        }

        return 'failed';
    }

    public function summary(Request $request)
{
    $validated = $request->validate([
        'course_id' => 'required|exists:courses,id',
    ]);

    $course = Course::find($validated['course_id']);

    // Assuming tuition_fee is defined on the Course model
    $tuitionFee = $course->tuition_fee;

    $totalPaid = TuitionHistory::where('course_id', $validated['course_id'])
        ->where('user_id', auth()->id())
        ->sum('amount_paid');

    $remainingBalance = $tuitionFee - $totalPaid;

    return response()->json([
        'tuition_fee' => $tuitionFee,
        'total_paid' => $totalPaid,
        'remaining_balance' => $remainingBalance,
    ]);
}

    function getTotalPaidForCourse(int $courseId, int $studentId): float
    {
        return TuitionHistory::where('course_id', $courseId)
            ->where('user_id', $studentId)
            ->sum('amount_paid');
    }

}
