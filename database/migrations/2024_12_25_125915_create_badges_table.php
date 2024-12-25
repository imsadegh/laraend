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
        Schema::create('badges', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name'); // Badge name (e.g., "Course Completion", "Top Performer")
            $table->string('description')->nullable(); // Optional description of the badge
            $table->string('icon_url')->nullable(); // URL for the badge's icon image
            $table->enum('type', ['achievement', 'participation', 'performance'])->default('achievement'); // Type of badge
            $table->json('criteria')->nullable(); // JSON field to store criteria (e.g., "complete 5 courses", "attend 80% of sessions")
            $table->timestamps(); // created_at and updated_at

            // Additional Considerations
            $table->boolean('is_public')->default(true); // Whether the badge is publicly visible on the user's profile
            $table->boolean('is_automated')->default(false); // Whether the badge is awarded automatically (e.g., via system triggers)
            $table->integer('points_value')->nullable(); // Points associated with the badge (for gamification systems)
            $table->integer('level')->default(1); // Level associated with the badge (e.g., Beginner, Intermediate, Advanced)
            $table->foreignId('awarded_by')->nullable()->constrained('users')->onDelete('set null'); // Who awarded the badge (could be an admin or the system)
            $table->timestamp('date_awarded')->nullable(); // Date when the badge was awarded

            // Indexes for optimized querying
            $table->index('type'); // Index for filtering by badge type (achievement, performance, participation)
            $table->index('level'); // Index for sorting badges by level
            $table->index('is_public'); // Index for filtering by public badges
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
