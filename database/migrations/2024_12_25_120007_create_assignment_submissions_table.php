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

            // Foreign keys
            $table->foreignId('assignment_id')
                  ->constrained('assignments')
                  ->cascadeOnDelete();
              // Deleting an assignment removes its submissions
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
              // Deleting a user removes their submissions

            // Basic submission info
            $table->timestamp('submission_date')->nullable();  // When the student submitted
            $table->string('file_path')->nullable();           // File path or storage link
            $table->text('comments')->nullable();              // Comments from the student

            // Grading fields
            $table->decimal('score', 5, 2)->nullable();        // Score for this submission
            $table->integer('revision_number')->default(1);    // Track how many times resubmitted
            $table->boolean('is_late')->default(false);        // Whether the submission was late
            $table->json('feedback')->nullable();              // JSON for teacher/assistant feedback

            // Additional considerations
            $table->timestamp('last_reviewed_at')->nullable(); // When it was last graded/reviewed
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
              // The teacher/assistant who reviewed it; null if that user is deleted

            $table->boolean('grade_visibility')->default(true); // Whether grade is visible to student
            $table->json('metadata')->nullable();               // Extra metadata (e.g., file size, extra logs)

            // Standard timestamps
            $table->timestamps();

            // Optional soft deletes to logically remove submissions instead of permanently
            // $table->softDeletes();

            // Indexes for quicker lookups
            $table->index('assignment_id');
            $table->index('user_id');
            $table->index('is_late');
            $table->index('reviewed_by');
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
