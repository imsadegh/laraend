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
        Schema::create('course_content', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // FK to Courses table
            $table->string('title'); // Title of the content (e.g., "Introduction to JavaScript")
            $table->enum('content_type', ['file', 'link', 'text', 'video', 'image'])->default('file'); // Type of content (e.g., file, link, etc.)
            $table->string('json_file_path'); // Path to the JSON file containing course content
            $table->text('description')->nullable(); // Description of the content (e.g., summary or instructions)
            $table->integer('position')->default(0); // Position/order of the content in the course (useful for displaying content in a sequence)

            // Additional Fields
            $table->string('version')->nullable(); // Version of the content (useful for tracking updates to the content)
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status of the content (active or inactive)
            $table->timestamps(); // created_at and updated_at

            // Indexes for optimized querying
            $table->index('course_id'); // Index for faster querying by course
            $table->index('content_type'); // Index for filtering by content type (e.g., files, links)
            $table->index('position'); // Index for sorting content by position in the course
            $table->index('status'); // Index for filtering by content status
            $table->index('version'); // Index for tracking different versions of content
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_content');
    }
};
