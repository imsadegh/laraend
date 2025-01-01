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

            // Link to 'courses' table
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->cascadeOnDelete();
              // If the course is deleted, remove its assignments as well.

            // Core assignment fields
            $table->string('title');                       // Assignment title
            $table->text('description')->nullable();       // Assignment description
            $table->timestamp('submission_deadline')->nullable(); // Submission deadline
            $table->json('requirements')->nullable();      // e.g., rubrics, special instructions

            // Use decimal for scoring to avoid float precision issues
            $table->decimal('max_score', 5, 2)->default(100.00)
                  ->comment('Maximum score achievable');
            $table->boolean('is_active')->default(true);   // Whether the assignment is active

            // Additional considerations
            $table->enum('type', ['individual', 'group'])->default('individual');
            $table->boolean('allow_late_submission')->default(false);
            $table->integer('late_submission_penalty')->nullable()
                  ->comment('Penalty % for late submissions');
            $table->json('resources')->nullable();         // JSON for storing links or files
            $table->integer('revision_limit')->default(1);
            $table->timestamp('published_at')->nullable(); // When the assignment was published
            $table->timestamp('last_submission_at')->nullable(); // Last allowed submission time

            // Standard timestamps
            $table->timestamps();

            // Uncomment if you want logical (soft) deletion instead of hard deletion
            $table->softDeletes();

            // Indexes for common queries
            $table->index('submission_deadline');
            $table->index('type');
            $table->index('is_active');
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
