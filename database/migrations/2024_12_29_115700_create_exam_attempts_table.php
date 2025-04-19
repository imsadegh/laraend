<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->tinyInteger('attempt_number')->default(1);
            // $table->decimal('score_total', 5, 2)->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->boolean('is_submitted')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['exam_id', 'user_id', 'attempt_number'], 'unique_exam_attempt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
