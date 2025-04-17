<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();  // Optional short title or label
            $table->text('question_text');        // The main question text
            $table->enum('type', ['multiple_choice', 'short_answer', 'true_false', 'essay'])
                ->default('multiple_choice');    // Question type
            $table->json('options')->nullable();  // For MCQ or multi-select (array of possible answers)
            $table->json('correct_answers')->nullable();
            // Could store correct options or short-answer solutions
            // You might add 'created_by' if you want to track question author
            // $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
