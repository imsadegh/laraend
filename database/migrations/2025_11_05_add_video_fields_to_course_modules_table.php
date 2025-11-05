<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('course_modules', function (Blueprint $table) {
            // Video URL management fields
            $table->text('encrypted_video_url')->nullable()->after('content_url'); // AES-256 encrypted URL
            $table->string('video_title')->nullable()->after('encrypted_video_url'); // Title for display
            $table->integer('estimated_duration_seconds')->nullable()->after('video_title'); // Duration in seconds
            $table->string('video_source')->nullable()->after('estimated_duration_seconds'); // 'youtube', 'vimeo', 'external', etc.
            $table->timestamp('video_added_at')->nullable()->after('video_source');
            $table->foreignId('video_added_by')->nullable()->constrained('users')->nullOnDelete()->after('video_added_at');
            $table->json('video_metadata')->nullable()->after('video_added_by'); // Thumbnail URL, etc.

            // Add indexes for better query performance
            $table->index('video_source');
            $table->index(['course_id', 'video_source']);
        });
    }

    public function down(): void
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'video_source']);
            $table->dropIndex('course_modules_video_source_index');
            $table->dropColumn([
                'encrypted_video_url',
                'video_title',
                'estimated_duration_seconds',
                'video_source',
                'video_added_at',
                'video_added_by',
                'video_metadata',
            ]);
        });
    }
};
