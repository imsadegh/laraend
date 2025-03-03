<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Deleting an assignment removes its submissions
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            // Deleting a user removes their submissions
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamp('submission_date')->nullable();
            $table->string('file_path')->nullable(); // File path or storage link
            $table->text('comments')->nullable(); // Comments from the student
            $table->decimal('score', 5, 2)->nullable();
            $table->integer('revision_number')->default(1); // Track how many times resubmitted
            $table->boolean('is_late')->default(false); // Whether the submission was late
            $table->json('feedback')->nullable(); // JSON for Instructor/assistant feedback
            $table->timestamp('last_reviewed_at')->nullable(); // When it was last graded/reviewed
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('grade_visibility')->default(true); // Whether grade is visible to student
            $table->json('metadata')->nullable(); // Extra metadata (e.g., file size, extra logs)

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('assignment_id');
            $table->index('user_id');
            $table->index('is_late');
            $table->index('reviewed_by');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
