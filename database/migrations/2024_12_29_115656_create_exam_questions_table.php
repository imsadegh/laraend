<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')
                ->constrained('exams')
                ->onDelete('cascade');
            $table->foreignId('question_id')
                ->constrained('questions')
                ->onDelete('cascade');

            $table->integer('position')->nullable()->comment('Ordering in the exam');
            $table->boolean('is_required')->default(true)->comment('If question is mandatory');
            // You could store partial credit info or scoring weight here if needed:
            // $table->decimal('question_weight', 5, 2)->default(1.0);

            $table->timestamps();
            $table->softDeletes();

            // If you don't want a question repeated in the same exam, add a unique constraint
            $table->unique(['exam_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
