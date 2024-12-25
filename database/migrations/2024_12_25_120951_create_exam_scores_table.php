<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_scores', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade'); // FK to Exams table
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK to Users table
            $table->float('score')->nullable(); // Score achieved by the student
            $table->boolean('is_passed')->default(false); // Whether the student passed the exam
            $table->json('score_details')->nullable(); // JSON for storing score breakdown (e.g., section-wise scores)
            $table->timestamp('submitted_at')->nullable(); // When the exam was submitted
            $table->boolean('is_finalized')->default(false); // Whether the score is finalized
            $table->timestamps(); // created_at and updated_at

            // Additional Considerations

            $table->timestamp('last_reviewed_at')->nullable(); // Timestamp of when the score was last reviewed
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // FK to Users table for reviewer (teacher/assistant)
            $table->boolean('is_graded_automatically')->default(false); // Whether the exam was graded automatically
            $table->json('grading_feedback')->nullable(); // Feedback provided by the reviewer
            $table->decimal('final_grade', 5, 2)->nullable(); // Final grade after re-evaluation or modifications
            $table->boolean('is_reexam') ->default(false); // Indicates whether the score is for a re-exam attempt

            // Indexes for optimized querying
            $table->index('exam_id'); // Index for faster querying by exam
            $table->index('user_id'); // Index for filtering by user
            $table->index('is_passed'); // Index for quickly filtering passed students
            $table->index('is_graded_automatically'); // Index for filtering automatic grading exams
            $table->index('is_reexam'); // Index for filtering re-exam scores
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_scores');
    }
};
