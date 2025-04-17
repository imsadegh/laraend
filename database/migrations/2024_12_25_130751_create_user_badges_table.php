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

            // Foreign keys linking user -> badge
            $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            $table->foreignId('badge_id')
                    ->constrained('badges')
                    ->cascadeOnDelete();

            // Track when this badge was awarded
            $table->timestamp('awarded_at')->useCurrent()
                    ->comment('Timestamp of when the badge was awarded');
            $table->timestamp('expires_at')->nullable()
                    ->comment('Expiration date/time for badges that aren’t permanent');

            // Level & metadata
            $table->integer('level')->default(1)
                    ->comment('Level assigned to this badge for the user (if badges can be leveled up)');
            $table->json('metadata')->nullable()
                    ->comment('Additional data or context about this specific badge award');

            // Approval & verification
            $table->boolean('is_approved')->default(true)
                    ->comment('Whether the badge was manually approved or auto-assigned');

            // Timestamps for record
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'badge_id']);


            // Indexes for quicker lookups
            $table->index('user_id');
            $table->index('badge_id');
            $table->index('awarded_at');
            $table->index('expires_at');
            $table->index('is_approved');
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
