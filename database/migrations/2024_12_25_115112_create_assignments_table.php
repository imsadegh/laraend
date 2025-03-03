<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();

            // Core assignment fields
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('submission_deadline')->nullable();
            $table->json('requirements')->nullable(); // e.g., rubrics, special instructions

            $table->decimal('max_score', 5, 2)->default(100.00)
                ->comment('Maximum score achievable');
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['individual', 'group'])->default('individual');
            $table->boolean('allow_late_submission')->default(false);
            $table->boolean('visible')->default(false);
            $table->integer('late_submission_penalty')->nullable()
                ->comment('Penalty % for late submissions');
            $table->json('resources')->nullable(); // JSON for storing links or files
            $table->integer('revision_limit')->default(1);
            $table->timestamp('published_at')->nullable(); // When the assignment was published
            $table->timestamp('last_submission_at')->nullable(); // Last allowed submission time

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('course_id');
            $table->index('submission_deadline');
            $table->index('type');
            $table->index('is_active');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
