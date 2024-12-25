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
            $table->string('name'); // Course name
            $table->string('code')->unique(); // Unique course code (e.g., CS101)
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade'); // Teacher of the course
            $table->foreignId('assistant_id')->nullable()->constrained('users')->onDelete('set null'); // Optional assistant
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null'); // Course category
            $table->integer('capacity')->nullable(); // Maximum number of students
            $table->boolean('visibility')->default(true); // Whether the course is visible to students
            $table->boolean('featured')->default(false); // Whether the course is featured
            $table->text('description')->nullable(); // Course description
            $table->text('about')->nullable(); // More detailed course information
            $table->string('discussion_group_url')->nullable(); // URL for discussion groups
            $table->timestamps(); // created_at and updated_at

            // Adding additional considerations
            $table->integer('enrolled_students_count')->default(0); // Tracks current enrollment
            $table->boolean('allow_waitlist')->default(true); // If waitlist is allowed when full
            $table->timestamp('start_date')->nullable(); // Start date for the course
            $table->timestamp('end_date')->nullable(); // End date for the course
            $table->json('prerequisites')->nullable(); // JSON for required prior courses
            $table->json('tags')->nullable(); // JSON array of tags for categorization and search
            $table->string('thumbnail_url')->nullable(); // URL for the course thumbnail
            $table->float('rating')->default(0)->comment('Average course rating'); // Average rating

        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
