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
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Link to courses table
            // If a course is deleted, its modules are removed.
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // FK to Courses table

            // (Optional) track the user who created or manages this module
            // If this user is deleted, set created_by to null.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Module classification and content
            $table->enum('type', ['video', 'article', 'quiz', 'assignment'])->default('video'); // Module type
            $table->string('title'); // Module title
            $table->text('content_url')->nullable(); // URL or path to the content
            $table->json('module_data')->nullable(); // Additional metadata (e.g., duration, requirements)

            // Module ordering & visibility
            $table->integer('position')->nullable(); // Order of the module in the course
            $table->boolean('visible')->default(true); // Visibility flag

            // Scheduling & progress tracking
            $table->timestamp('release_date')->nullable(); // Scheduled release date
            $table->boolean('is_mandatory')->default(false); // If module is required to complete the course
            $table->integer('estimated_duration_minutes')->nullable(); // Estimated time to complete the module

            // Tracking usage
            $table->integer('view_count')->default(0); // Tracks how many times the module is accessed
            $table->json('prerequisite_modules')->nullable(); // JSON list of prerequisite module IDs

            // Use decimal for rating to avoid float precision issues
            $table->decimal('rating', 3, 2)->default(0)->comment('Average module rating, e.g., 0.00 to 5.00');

            // Unique slug
            $table->string('slug')->unique()->nullable(); // Unique slug for module identification

            $table->timestamps(); // created_at and updated_at
            // (Optional) Enable soft deletes for logical removal
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_modules');
    }
};
