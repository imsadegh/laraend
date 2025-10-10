<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Assignment;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Create assignments without factory (production-safe)
        Assignment::create([
            'title' => 'تمرین اول',
            'description' => 'این تمرین اول برای دوره مقدماتی ویو می‌باشد.',
            'course_id' => 1,
            'max_score' => 100,
            'is_active' => true,
            'visible' => true,
            'type' => 'individual',
            'submission_deadline' => now()->addDays(30),
        ]);

        Assignment::create([
            'title' => 'تکلیف اول',
            'description' => 'این تمرین اول برای دوره پیشرفته لاراول می‌باشد.',
            'course_id' => 4,
            'max_score' => 100,
            'is_active' => true,
            'visible' => true,
            'type' => 'individual',
            'submission_deadline' => now()->addDays(30),
        ]);

        Assignment::create([
            'title' => 'تمرین دوم',
            'description' => 'این تمرین دوم برای دوره مقدماتی ویو می‌باشد.',
            'course_id' => 1,
            'max_score' => 100,
            'is_active' => true,
            'visible' => true,
            'type' => 'group',
            'submission_deadline' => now()->addDays(45),
        ]);

        // Additional assignments (only if Faker is available - for development)
        if (class_exists('\Faker\Factory')) {
            Assignment::factory(2)->create();
        }
    }
}
