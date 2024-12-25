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
        Schema::create('course_watch_time', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK to Users table
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // FK to Courses table
            $table->integer('watch_time_minutes')->default(0); // Total watch time in minutes
            $table->timestamp('last_watched_at')->nullable(); // Last time the student watched the course
            $table->timestamps(); // created_at and updated_at

            // Additional Considerations
            $table->enum('completion_status', ['in_progress', 'completed'])->default('in_progress'); // Completion status
            $table->string('device_type')->nullable(); // Type of device used (e.g., mobile, desktop)
            $table->string('platform')->nullable(); // Platform used (e.g., Web, iOS, Android)
            $table->json('metadata')->nullable(); // Extra metadata (e.g., course-specific data, chapter details)
            $table->boolean('is_complete')->default(false); // Whether the student has completed the course or module
            $table->decimal('progress_percentage', 5, 2)->default(0); // Progress in percentage (0-100%)

            // Indexes for optimized querying
            $table->index('user_id'); // Index for faster querying by user
            $table->index('course_id'); // Index for faster querying by course
            $table->index('last_watched_at'); // Index for querying by last time a course was watched
            $table->index('completion_status'); // Index for filtering by completion status
            $table->index('device_type'); // Index for device usage filtering
            $table->index('platform'); // Index for platform-based filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_watch_time');
    }
};
