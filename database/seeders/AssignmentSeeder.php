<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Assignment;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        Assignment::factory()->create([
            'title' => 'تمرین اول',
            'description' => 'این تمرین اول برای دوره مقدماتی ویو می‌باشد.',
            'course_id' => 1,
        ]);
        Assignment::factory()->create([
            'title' => 'تمرین دوم',
            'description' => 'این تمرین دوم برای دوره مقدماتی ویو می‌باشد.',
            'course_id' => 1,
        ]);
        Assignment::factory()->create([
            'title' => 'تکلیف اول',
            'description' => 'این تمرین اول برای دوره مقدماتی ویو می‌باشد.',
            'course_id' => 3,
        ]);

        // Generate 10 assignments for each course
        Assignment::factory(count: 2)->create();
    }
}
