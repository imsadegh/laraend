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
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK to Users table
            $table->enum('activity_type', ['login', 'viewed_course', 'submitted_assignment', 'completed_exam', 'course_enrolled', 'profile_updated', 'logout', 'other']); // Type of activity
            $table->text('activity_details')->nullable(); // Detailed information about the activity (e.g., course name, IP address, etc.)
            $table->string('ip_address', 45)->nullable(); // IP address of the user during the activity
            $table->text('user_agent')->nullable(); // User agent for browser/device details
            $table->timestamp('activity_time')->useCurrent(); // Timestamp of when the activity occurred
            $table->timestamps(); // created_at and updated_at

            // Additional Considerations

            $table->string('session_id')->nullable(); // Track session ID for a particular user session
            $table->json('activity_metadata')->nullable(); // Store extra metadata for more complex activities
            $table->integer('duration_seconds')->nullable(); // Duration in seconds for activities that involve time spent (e.g., time spent viewing a course)
            $table->boolean('is_successful')->default(true); // Indicates if the activity was successful (e.g., login success)
            $table->string('referrer_url')->nullable(); // Tracks the referrer URL for the activity (e.g., where the user came from)
            $table->enum('activity_category', ['system', 'user_interaction', 'content_interaction', 'other'])->default('user_interaction'); // Categorizes activity for reporting

            // Indexes for optimized querying
            $table->index('user_id'); // Index for faster querying by user
            $table->index('activity_type'); // Index for filtering by activity type
            $table->index('activity_time'); // Index for querying by timestamp (for activity logs in a certain time range)
            $table->index('is_successful'); // Index for filtering successful vs failed activities
            $table->index('activity_category'); // Index for filtering by activity category
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
