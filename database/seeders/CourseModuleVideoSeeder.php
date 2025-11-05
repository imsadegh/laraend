<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CourseModule;
use App\Services\EncryptionService;

class CourseModuleVideoSeeder extends Seeder
{
    public function run(): void
    {
        $encryptionService = app(EncryptionService::class);

        // Reset the sequence for PostgreSQL to prevent ID conflicts
        try {
            $maxId = DB::table('course_modules')->max('id') ?? 0;
            DB::statement("ALTER SEQUENCE course_modules_id_seq RESTART WITH " . ($maxId + 1));
        } catch (\Exception $e) {
            // If not PostgreSQL or sequence doesn't exist, continue
        }

        // Update existing module ID 2 with video data
        $module2 = CourseModule::find(2);
        if ($module2) {
            $module2->update([
                'encrypted_video_url' => $encryptionService->encryptUrl('https://youtu.be/dQw4w9WgXcQ'),
                'video_title' => 'آموزش مقدماتی Vue.js',
                'estimated_duration_seconds' => 420, // 7 minutes
                'video_source' => 'youtube',
                'video_added_by' => 1,
                'video_added_at' => now(),
                'video_metadata' => json_encode([
                    'thumbnail' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
                    'channel' => 'Vue.js Official',
                ]),
            ]);
        }

        // Create additional video modules
        CourseModule::create([
            'course_id' => 1,
            'created_by' => 1,
            'title' => 'مقدمات پیشرفته',
            'type' => 'video',
            'content_url' => null,
            'description' => 'درس دوم: مفاهیم پیشرفته Vue.js',
            'article_content' => null,
            'module_data' => null,
            'position' => 3,
            'visible' => true,
            'release_date' => now(),
            'is_mandatory' => false,
            'estimated_duration_minutes' => 15,
            'view_count' => 0,
            'prerequisite_modules' => json_encode([2]),
            'rating' => 0.00,
            'encrypted_video_url' => $encryptionService->encryptUrl('https://vimeo.com/123456789'),
            'video_title' => 'کمپوننت‌های پیشرفته در Vue',
            'estimated_duration_seconds' => 900,
            'video_source' => 'vimeo',
            'video_added_by' => 1,
            'video_added_at' => now(),
            'video_metadata' => json_encode([
                'thumbnail' => 'https://i.vimeocdn.com/video/123456789.webp',
                'instructor' => 'محمد علی',
            ]),
        ]);

        // Create external CDN video module
        CourseModule::create([
            'course_id' => 1,
            'created_by' => 1,
            'title' => 'دوره کامل Vue.js',
            'type' => 'video',
            'content_url' => null,
            'description' => 'آموزش کامل Vue.js از پایه تا متقدم',
            'article_content' => null,
            'module_data' => null,
            'position' => 4,
            'visible' => true,
            'release_date' => now(),
            'is_mandatory' => true,
            'estimated_duration_minutes' => 120,
            'view_count' => 0,
            'prerequisite_modules' => null,
            'rating' => 4.5,
            'encrypted_video_url' => $encryptionService->encryptUrl('https://file-examples.com/wp-content/storage/2017/04/file_example_MP4_480_1_5MG.mp4'),
            'video_title' => 'دوره کامل Vue.js - قسمت 1',
            'estimated_duration_seconds' => 7200,
            'video_source' => 'external',
            'video_added_by' => 1,
            'video_added_at' => now(),
            'video_metadata' => json_encode([
                'thumbnail' => 'https://file-examples.com/wp-content/uploads/2017/04/file_example_MP4_480_1_5MG.jpg',
                'quality' => '480p',
                'language' => 'persian',
            ]),
        ]);

        // Add Laravel video for course 2 if it exists
        $course2 = \App\Models\Course::find(2);
        if ($course2) {
            CourseModule::create([
                'course_id' => 2,
                'created_by' => 2,
                'title' => 'مقدمه به Laravel',
                'type' => 'video',
                'content_url' => null,
                'description' => 'شروع سفر خود با Laravel',
                'article_content' => null,
                'module_data' => null,
                'position' => 1,
                'visible' => true,
                'release_date' => now(),
                'is_mandatory' => true,
                'estimated_duration_minutes' => 45,
                'view_count' => 0,
                'prerequisite_modules' => null,
                'rating' => 0.00,
                'encrypted_video_url' => $encryptionService->encryptUrl('https://youtu.be/w7ysrFQ3kV0'),
                'video_title' => 'Laravel برای مبتدیان',
                'estimated_duration_seconds' => 2700,
                'video_source' => 'youtube',
                'video_added_by' => 2,
                'video_added_at' => now(),
                'video_metadata' => json_encode([
                    'playlist' => 'Laravel Fundamentals',
                    'instructor' => 'Taylor Otwell',
                ]),
            ]);
        }
    }
}
