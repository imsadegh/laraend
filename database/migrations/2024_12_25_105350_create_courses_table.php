<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->string('course_name'); // Name of the course
            $table->string('course_code')->unique(); // Unique course code (for identification)

            // Foreign keys referencing the 'users' table
            // If teacher is deleted, remove course or reassign logic
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade'); // Teacher of the course
            // If assistant is deleted, set this null
            $table->foreignId('assistant_id')->nullable()->constrained('users')->nullOnDelete(); // Optional assistant
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete(); // FK to the instructor (user)

            // Foreign key for categories (rename if you have a different table name)
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // FK to course categories table

            // Additional course fields
            $table->integer('capacity')->nullable(); // Maximum number of students
            $table->boolean('visibility')->default(true); // Whether the course is visible to students
            $table->boolean('featured')->default(false); // Whether the course is featured
            $table->text('description')->nullable(); // Description of the course
            $table->text('about')->nullable(); // More detailed course information
            $table->string('discussion_group_url')->nullable(); // URL for discussion groups
            $table->enum('status', ['active', 'archived', 'draft'])->default('active'); // Status of the course

            // Timestamps and optional soft deletes
            $table->softDeletes(); // Uncomment if you want "deleted_at" for soft deletions
            $table->timestamps(); // created_at and updated_at

            // Adding additional considerations
            $table->integer('enrolled_students_count')->default(0); // Tracks current enrollment
            $table->boolean('allow_waitlist')->default(true); // If waitlist is allowed when full
            $table->timestamp('start_date')->nullable(); // Start date for the course
            $table->timestamp('end_date')->nullable(); // End date for the course
            $table->json('prerequisites')->nullable(); // JSON for required prior courses
            $table->json('tags')->nullable(); // JSON array of tags for categorization and search
            $table->string('thumbnail_url')->nullable(); // URL for the course thumbnail
            // $table->float('rating')->default(0)->comment('Average course rating'); // Average rating
            $table->decimal('rating', 3, 2)->default(0)->comment('Average course rating from 0 to 5');

            // Indexes for optimized querying
            $table->index('course_code'); // Index for quick lookups by course code
            $table->index('status'); // Index for filtering by course status
            $table->index('category_id'); // Index for filtering by category
            $table->index('instructor_id'); // Index for filtering by instructor
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
