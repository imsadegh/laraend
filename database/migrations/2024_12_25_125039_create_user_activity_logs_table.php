<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->foreignId('user_id')->constrained('users')->nullOnDelete();

            // Core activity fields
            $table->enum('activity_type', [
                'login',
                'viewed_course',
                'submitted_assignment',
                'completed_exam',
                'course_enrolled',
                'profile_updated',
                'logout',
                'other'
            ]);
            $table->text('activity_details')->nullable(); // Additional info about the action
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('activity_time')->useCurrent();

            $table->string('session_id')->nullable();
            $table->json('activity_metadata')->nullable(); // JSON for extra metadata
            $table->integer('duration_seconds')->nullable();
            // Duration of the activity (e.g., time spent viewing a course)
            $table->boolean('is_successful')->default(true);
            // Was the activity successful or did it fail?
            $table->string('referrer_url')->nullable();
            // The URL that referred the user to this activity

            // For categorizing activity at a broader level
            $table->enum('activity_category', [
                'system',
                'user_interaction',
                'content_interaction',
                'other'
            ])->default('user_interaction');

            $table->timestamps();

            // Indexes for frequent queries
            $table->index('user_id');
            $table->index('activity_type');
            $table->index('activity_time');
            $table->index('is_successful');
            $table->index('activity_category');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
