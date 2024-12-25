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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // FK to Courses table
            $table->string('title'); // Assignment title
            $table->text('description')->nullable(); // Assignment description
            $table->timestamp('submission_deadline')->nullable(); // Submission deadline
            $table->json('requirements')->nullable(); // JSON for storing assignment-specific requirements
            $table->integer('max_score')->default(100); // Maximum score achievable
            $table->boolean('is_active')->default(true); // Whether the assignment is active

            // Additional considerations
            $table->enum('type', ['individual', 'group'])->default('individual'); // Assignment type
            $table->boolean('allow_late_submission')->default(false); // Whether late submissions are allowed
            $table->integer('late_submission_penalty')->nullable(); // Penalty percentage for late submissions
            $table->json('resources')->nullable(); // JSON for storing links or files related to the assignment
            $table->integer('revision_limit')->default(1); // Maximum number of revisions allowed
            $table->timestamp('published_at')->nullable(); // When the assignment was published
            $table->timestamp('last_submission_at')->nullable(); // Last possible submission time

            $table->timestamps(); // created_at and updated_at

            // Indexes for optimized querying
            $table->index('submission_deadline'); // Index for faster querying by deadline
            $table->index('type'); // Index for filtering by assignment type
            $table->index('is_active'); // Index for checking active assignments
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
