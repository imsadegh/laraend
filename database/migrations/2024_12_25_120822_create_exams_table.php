<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            $table->string('name');
            $table->text('intro')->nullable(); // Introduction or description of the exam
            $table->timestamp('time_open');
            $table->timestamp('time_close');
            $table->integer('time_limit');
            $table->integer('grade')->default(20); // Maximum grade (total score) for the exam
            $table->integer('questions_count')->default(0);
            $table->enum('exam_type', ['multiple_choice', 'short_answer', 'true_false', 'essay'])->default('multiple_choice');
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_answers')->default(true);
            $table->integer('attempts')->default(1);
            $table->boolean('feedback_enabled')->default(true);
            $table->integer('version')->default(1); // Version number for the exam (useful for tracking updates)
            $table->json('question_pool')->nullable(); // JSON to store random question pool if applicable
            $table->enum('status', ['active', 'inactive', 'draft'])->default('inactive');
            $table->boolean('is_published')->default(false); // Indicates if the exam is published or not
            // $table->string('time_zone')->default('Asia/Tehran'); // Store time zone for the exam

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for optimized querying
            $table->index('course_id');
            $table->index('exam_type');
            $table->index('time_open');
            $table->index('version');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
