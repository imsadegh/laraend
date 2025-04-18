<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_watch_time', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();

            // Core tracking fields
            $table->integer('watch_time_minutes')->default(0)
                ->comment('Accumulated total watch time in minutes');
            $table->timestamp('last_watched_at')->nullable()
                ->comment('Last timestamp the user watched the course content');

            $table->enum('completion_status', ['in_progress', 'completed'])
                ->default('in_progress')
                ->comment('Tracks user’s completion status for the course');

            // Additional metadata
            $table->string('device_type')->nullable()
                ->comment('Device used: mobile, desktop, etc.');
            $table->string('platform')->nullable()
                ->comment('Platform used: Web, iOS, Android, etc.');
            $table->json('metadata')->nullable()
                ->comment('Flexible JSON for storing extra data (e.g., chapters watched)');

            // Use decimal for fractional progress to avoid float rounding issues
            $table->decimal('progress_percentage', 5, 2)
                ->default(0.00)
                ->comment('User’s progress in the course (0.00–100.00)');


            $table->timestamps();
            $table->softDeletes();

            // Indexes for frequent queries
            $table->index('user_id');
            $table->index('course_id');
            $table->index('completion_status');
            $table->index('device_type');
            $table->index('platform');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('course_watch_time');
    }
};
