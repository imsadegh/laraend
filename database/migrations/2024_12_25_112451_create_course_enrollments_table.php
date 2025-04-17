<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id(); // Primary Key

            // If a course is deleted, null related enrollments
            $table->foreignId('course_id')->constrained('courses')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Enrollment status fields
            $table->enum('status', ['enrolled', 'waitlisted', 'completed', 'dropped', 'pending'])
                ->default('pending');
            $table->boolean('active')->default(true); // If the enrollment is currently active
            // Whether user is allowed to enroll in the course again or continue:
            $table->boolean('eligible_for_enrollment')->default(true);

            // Dates & progress
            $table->date('enrollment_date')->nullable();
            $table->timestamp('completion_date')->nullable();
            $table->timestamp('last_accessed_at')->nullable(); // Last time user accessed the course
            $table->integer('progress_percentage')->default(0); // 0–100 progress
            $table->decimal('final_score', 5, 2)->nullable(); // Course final score/grade
            $table->json('certificate_data')->nullable();  // e.g., certificate ID, URL, or meta info
            $table->unique(['course_id', 'user_id']); // Ensure one enrollment record per course-user pair

            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index('status');
            $table->index('active');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
