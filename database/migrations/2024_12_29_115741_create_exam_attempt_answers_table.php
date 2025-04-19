<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');

            // The student's actual response:
            $table->text('answer_text')->nullable();
            // For essay or short-answer
            $table->string('selected_option')->nullable();
            // For multiple_choice / true_false if they pick "A" or "True"
            $table->boolean('is_correct')->default(false);
            // Or store correctness if you auto-grade multiple-choice
            $table->decimal('score_earned', 5, 2)->nullable()->comment('Points for this question');

            $table->timestamps();
            $table->softDeletes();

            // If you want to enforce only 1 record of (exam_id, user_id, question_id) per attempt:
            $table->unique(['exam_attempt_id', 'user_id', 'question_id'], 'unique_exam_attempt_answer');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempt_answers');
    }
};
