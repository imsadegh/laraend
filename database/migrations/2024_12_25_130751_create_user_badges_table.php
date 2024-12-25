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
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK to Users table
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade'); // FK to Badges table
            $table->timestamp('awarded_at')->useCurrent(); // Timestamp of when the badge was awarded
            $table->timestamp('expires_at')->nullable(); // Expiration date for badges (if applicable)
            $table->integer('level')->default(1); // Level associated with the badge (useful for progressive badges)
            $table->json('metadata')->nullable(); // Additional metadata for flexibility (e.g., custom data, achievement criteria)
            $table->boolean('is_approved')->default(true); // Whether the badge is manually approved (can be false for automated badges)
            $table->timestamps(); // created_at and updated_at

            // Indexes for optimized querying
            $table->index('user_id'); // Index for faster querying by user
            $table->index('badge_id'); // Index for faster querying by badge
            $table->index('awarded_at'); // Index for querying by awarded date
            $table->index('expires_at'); // Index for filtering by expiration date
            $table->index('is_approved'); // Index for filtering by approval status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
