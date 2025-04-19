<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_scores', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Links to 'exams' and 'users'
            $table->foreignId('exam_id') ->constrained('exams') ->cascadeOnDelete();
            $table->foreignId('user_id') ->constrained('users') ->cascadeOnDelete();

            // Core scoring fields
            $table->decimal('score', 5, 2)->nullable()
                ->comment('Raw or initial score earned');
            $table->boolean('is_passed')->default(false);
            $table->boolean('is_finalized')->default(false)
                ->comment('Whether the score is finalized');
            $table->boolean('is_graded_automatically')->default(false)
                ->comment('True if system-graded, false if manually graded');
            $table->decimal('final_grade', 5, 2)->nullable()
                ->comment('Final adjusted grade after reviews or re-checks');

            // Timestamps for exam submission, reviewing, etc.
            // $table->timestamp('submitted_at')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();

            // Grading feedback, details, re-exam flag
            $table->json('score_details')->nullable()
                ->comment('Breakdown of partial scores, question by question if needed');
            $table->json('grading_feedback')->nullable()
                ->comment('Instructor or system feedback on performance');
            $table->boolean('is_reexam')->default(false)
                ->comment('Indicates if this record is for a re-exam attempt');

            // Reviewer reference (Instructor or assistant)
            $table->foreignId('reviewed_by') ->nullable() ->constrained('users')
                ->nullOnDelete() ->comment('User ID of the person who reviewed/graded the exam');

            // Standard timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for optimized querying
            $table->index('exam_id');
            $table->index('user_id');
            $table->index('is_passed');
            $table->index('is_graded_automatically');
            $table->index('is_reexam');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_scores');
    }
};
