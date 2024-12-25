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
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // FK to Courses table
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK to Users table (students)
            $table->enum('status', ['enrolled', 'waitlisted', 'completed', 'dropped'])->default('enrolled'); // Enrollment status
            $table->timestamp('completion_date')->nullable(); // Completion timestamp
            $table->boolean('eligible_for_enrollment')->default(true); // Eligibility flag
            $table->timestamps(); // created_at and updated_at

            // Composite unique index for course_id and user_id to prevent duplicate enrollments
            $table->unique(['course_id', 'user_id']);

            // Adding additional considerations
            $table->date('enrollment_date')->nullable(); // Date of enrollment
            $table->boolean('active')->default(true); // Whether the enrollment is active
            $table->integer('progress_percentage')->default(0); // Progress in the course (0–100)
            $table->float('final_score')->nullable(); // Final score for the course
            $table->timestamp('last_accessed_at')->nullable(); // Last time the student accessed the course
            $table->json('certificate_data')->nullable(); // JSON data for course completion certificate
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
