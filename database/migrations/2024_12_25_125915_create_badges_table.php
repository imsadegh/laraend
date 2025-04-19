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

            // Core badge info
            $table->string('name');                 // e.g., "Course Completion", "Top Performer"
            $table->string('description')->nullable();
            $table->string('icon_url')->nullable(); // URL/path for the badge icon
            $table->enum('type', ['achievement', 'participation', 'performance'])
                  ->default('achievement');
            $table->json('criteria')->nullable();   // e.g., JSON storing the requirements

            // Visibility & automation flags
            $table->boolean('is_public')->default(true);
            $table->boolean('is_automated')->default(false);

            // Points & leveling (for gamification)
            $table->integer('points_value')->nullable();
            $table->integer('level')->default(1);

            // Record who awarded the badge, if relevant
            $table->foreignId('awarded_by') ->nullable() ->constrained('users') ->nullOnDelete();
              // If the awarding user is deleted, we set this field to null

            // Track when the badge was awarded
            $table->timestamp('date_awarded')->nullable();

            // Standard timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('type');      // Filter badges by type
            $table->index('level');     // Sort/filter badges by level
            $table->index('is_public'); // Quickly find public badges
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
