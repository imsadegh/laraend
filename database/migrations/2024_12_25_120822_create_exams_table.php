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
        Schema::create('exams', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // FK to the course

            $table->string('name'); // Name of the exam
            $table->text('intro')->nullable(); // Introduction or description of the exam
            $table->timestamp('time_open')->nullable(); // When the exam opens
            $table->timestamp('time_close')->nullable(); // When the exam closes
            $table->integer('time_limit')->nullable(); // Time limit for the exam in seconds
            $table->integer('grade')->default(100); // Maximum grade (total score) for the exam
            $table->integer('questions_count')->default(0); // Number of questions in the exam
            $table->enum('exam_type', ['multiple_choice', 'short_answer', 'true_false', 'essay'])->default('multiple_choice'); // Type of exam
            $table->boolean('shuffle_questions')->default(true); // Whether the questions are shuffled
            $table->boolean('shuffle_answers')->default(true); // Whether the answers are shuffled
            $table->integer('attempts')->default(1); // Number of attempts allowed
            $table->boolean('feedback_enabled')->default(true); // Whether feedback is provided after the exam
            $table->integer('version')->default(1); // Version number for the exam (useful for tracking updates)
            $table->json('question_pool')->nullable(); // JSON to store random question pool if applicable
            $table->enum('status', ['active', 'archived', 'draft'])->default('active'); // Status of the exam
            $table->string('time_zone')->default('UTC'); // Store time zone for the exam

            // ??????????
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                  // If the user is deleted, we set created_by = null
                  // (instead of cascade deleting the exam).

            $table->timestamps(); // created_at and updated_at

            // Indexes for optimized querying
            $table->index('course_id'); // Index for filtering exams by course
            $table->index('exam_type'); // Index for filtering exams by type
            $table->index('time_open'); // Index for filtering exams by open date
            $table->index('time_close'); // Index for filtering exams by close date
            $table->index('feedback_enabled'); // Index for filtering by feedback setting
            $table->index('version'); // Index for tracking different versions of the exam
            $table->index('status'); // Index for filtering by exam status (active, archived, draft)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
