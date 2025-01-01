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
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign keys
            // If a course is deleted, remove related enrollments
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            // If a user is deleted, remove their course enrollments
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Enrollment status fields
            $table->enum('status', ['enrolled', 'waitlisted', 'completed', 'dropped'])
                  ->default('enrolled');
            $table->boolean('active')->default(true); // If the enrollment is currently active
            $table->boolean('eligible_for_enrollment')->default(true);
              // Whether user is allowed to enroll in the course again or continue

            // Dates & progress
            $table->date('enrollment_date')->nullable(); // The date the user enrolled (could differ from created_at)
            $table->timestamp('completion_date')->nullable(); // When the user actually completed the course
            $table->timestamp('last_accessed_at')->nullable(); // Last time user accessed the course
            $table->integer('progress_percentage')->default(0); // 0–100 progress
            $table->decimal('final_score', 5, 2)->nullable(); // Course final score/grade (avoid float precision issues)

            // Additional data
            $table->json('certificate_data')->nullable();  // e.g., certificate ID, URL, or meta info

            // Ensure one enrollment record per course-user pair
            $table->unique(['course_id', 'user_id']);

            $table->timestamps(); // created_at and updated_at

            // Add indexes if you query these fields often
            $table->index('status');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
