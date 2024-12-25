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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade'); // FK to Assignments table
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK to Users table
            $table->timestamp('submission_date')->nullable(); // Date of submission
            $table->string('file_path')->nullable(); // Path to the submitted file
            $table->text('comments')->nullable(); // Comments provided by the student
            $table->float('score')->nullable(); // Score assigned to the submission
            $table->integer('revision_number')->default(1); // Revision count for the submission
            $table->boolean('is_late')->default(false); // Whether the submission was late
            $table->json('feedback')->nullable(); // Feedback given by the teacher or assistant

            // Additional considerations
            $table->timestamp('last_reviewed_at')->nullable(); // Timestamp of when the submission was last reviewed
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // FK to Users table for reviewer (teacher/assistant)
            $table->boolean('grade_visibility')->default(true); // Whether the grade is visible to the student
            $table->json('metadata')->nullable(); // JSON for storing additional metadata (e.g., grading criteria, file size)

            $table->timestamps(); // created_at and updated_at

            // Indexes for optimized querying
            $table->index('assignment_id'); // Index for faster querying by assignment
            $table->index('user_id'); // Index for filtering by user
            $table->index('is_late'); // Index for checking late submissions
            $table->index('reviewed_by'); // Index for filtering by reviewer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
