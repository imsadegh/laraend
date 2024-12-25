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
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // FK to Courses table
            $table->enum('type', ['video', 'article', 'quiz', 'assignment'])->default('video'); // Module type
            $table->string('title'); // Module title
            $table->text('content_url')->nullable(); // URL or path to the content
            $table->json('module_data')->nullable(); // Additional metadata (e.g., duration, requirements)
            $table->integer('position')->nullable(); // Order of the module in the course
            $table->boolean('visible')->default(true); // Visibility flag
            $table->timestamps(); // created_at and updated_at

            // Adding additional considerations
            $table->string('slug')->unique()->nullable(); // Unique slug for module identification
            $table->timestamp('release_date')->nullable(); // Scheduled release date
            $table->boolean('is_mandatory')->default(false); // If module is mandatory for course completion
            $table->integer('estimated_duration_minutes')->nullable(); // Estimated time to complete the module
            $table->integer('view_count')->default(0); // Tracks how many times the module is accessed
            $table->json('prerequisite_modules')->nullable(); // JSON list of prerequisite module IDs
            $table->float('rating')->default(0)->comment('Average module rating'); // Rating of the module

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
