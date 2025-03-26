<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->string('course_name');
            $table->string('course_code')->unique();

            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete(); // FK to the instructor (user)
            $table->foreignId('assistant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->constrained('categories')->nullOnDelete(); // FK to course categories table

            // Additional course fields
            $table->decimal('tuition_fee', 10, 0)->default(0)->comment('Cost of the course');
            $table->integer('capacity')->nullable();
            $table->boolean('visibility')->default(true);
            $table->enum('status', ['active', 'archived', 'draft'])->default('active');
            $table->boolean('featured')->default(false); // Whether the course is featured
            $table->text('description')->nullable();
            $table->text('about')->nullable(); // More detailed course information
            $table->string('discussion_group_url')->nullable();
            $table->boolean('is_finished')->default(false);
            $table->integer('enrolled_students_count')->default(0);
            $table->enum('skill_level', ['beginner', 'intermediate', 'advanced'])->nullable();
            $table->boolean('is_free')->default(false);
            $table->integer('total_lectures')->nullable();

            $table->integer('lecture_length')->nullable();
            $table->integer('total_quizzes')->nullable();
            $table->integer('total_assignments')->nullable();
            $table->integer('total_resources')->nullable();
            $table->enum('language',['en','fr','ar','fa'] )->default('fa');
            $table->boolean('is_captions')->default(false);
            $table->boolean('is_certificate')->default(false);
            $table->boolean('is_quiz')->default(false);
            $table->boolean('is_assignment')->default(false);
            $table->json('table_of_content')->nullable();

            $table->boolean('allow_waitlist')->default(false); // If waitlist is allowed when full
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->json('prerequisites')->nullable();
            $table->json('tags')->nullable(); // JSON array of tags for categorization and search
            $table->string('thumbnail_url')->nullable();
            // $table->float('rating')->default(0)->comment('Average course rating'); // Average rating
            $table->decimal('rating', 3, 2)->default(0)->comment('Average course rating from 0 to 5');

            $table->softDeletes();
            $table->timestamps();

            // Indexes for optimized querying
            $table->index('course_name');
            $table->index('course_code');
            $table->index('status');
            $table->index('category_id');
            $table->index('instructor_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
