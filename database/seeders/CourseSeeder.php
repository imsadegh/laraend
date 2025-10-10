<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Create courses without factory (production-safe)
        Course::create([
            'course_name' => 'دوره مقدماتی ویو',
            'course_code' => 'VUE-001',
            'description' => 'این دوره برای آموزش ابتدایی Vue.js می‌باشد.',
            'about' => 'در این دوره مباحث پایه‌ای و ساخت کامپوننت آموزش داده می‌شود.',

            // [{"title":"مقدمه","status":"منتشر شده","time":"10:00","topics":[{"title":"ویو چیست؟","time":"5:00","isCompleted":false},{"title":"بررسی کلی پروژه","time":"5:00","isCompleted":false}]},{"title":"موضوعات پیشرفته","status":"پیش نویس","time":"20:00","topics":[{"title":"عمق واکنش پذیری","time":"10:00","isCompleted":false},{"title":"آپی ترکیبی","time":"10:00","isCompleted":false}]}]

        //     'table_of_content' => json_encode([
        // [
        //     'title' => 'مقدمه',
        //     'status' => 'منتشر شده',
        //     'time' => '10:00',
        //     'topics' => [
        //         [
        //             'title' => 'ویو چیست؟',
        //             'time' => '5:00',
        //             'isCompleted' => false,
        //         ],
        //         [
        //             'title' => 'بررسی کلی پروژه',
        //             'time' => '5:00',
        //             'isCompleted' => false,
        //         ],
        //     ],
        //         ],
        //         [
        //             'title' => 'موضوعات پیشرفته',
        //             'status' => 'پیش نویس',
        //             'time' => '20:00',
        //             'topics' => [
        //                 [
        //                     'title' => 'عمق واکنش پذیری',
        //                     'time' => '10:00',
        //                     'isCompleted' => false,
        //                 ],
        //                 [
        //                     'title' => 'آپی ترکیبی',
        //                     'time' => '10:00',
        //                     'isCompleted' => false,
        //                 ],
        //             ],
        //         ],
        //     ], JSON_UNESCAPED_UNICODE),


            'instructor_id' => 2,
            'category_id' => 1,
            'tuition_fee' => 5000000,
            'capacity' => 50,
            'visibility' => true,
            'status' => 'active',
            'skill_level' => 'beginner',
            'language' => 'fa',
        ]);

        Course::create([
            'course_name' => 'دوره پیشرفته ویو',
            'course_code' => 'VUE-002',
            'description' => 'این دوره برای آموزش پیشرفته Vue.js می‌باشد.',
            'about' => 'در این دوره مباحث پیشرفته و ساخت کامپوننت‌های پیچیده آموزش داده می‌شود.',
            'instructor_id' => 2,
            'category_id' => 1,
            'tuition_fee' => 8000000,
            'capacity' => 40,
            'visibility' => true,
            'status' => 'active',
            'skill_level' => 'advanced',
            'language' => 'fa',
        ]);

        Course::create([
            'course_name' => 'دوره مقدماتی لاراول',
            'course_code' => 'LAR-001',
            'description' => 'این دوره برای آموزش ابتدایی Laravel می‌باشد.',
            'about' => 'در این دوره مباحث پایه‌ای و ساخت برنامه‌های CRUD آموزش داده می‌شود.',
            'instructor_id' => 2,
            'category_id' => 2,
            'tuition_fee' => 6000000,
            'capacity' => 50,
            'visibility' => true,
            'status' => 'active',
            'skill_level' => 'beginner',
            'language' => 'fa',
        ]);

        Course::create([
            'course_name' => 'دوره پیشرفته لاراول',
            'course_code' => 'LAR-002',
            'description' => 'این دوره برای آموزش پیشرفته Laravel می‌باشد.',
            'about' => 'در این دوره مباحث پیشرفته و ساخت برنامه‌های پیچیده آموزش داده می‌شود.',
            'instructor_id' => 2,
            'category_id' => 2,
            'tuition_fee' => 9000000,
            'capacity' => 40,
            'visibility' => true,
            'status' => 'active',
            'skill_level' => 'advanced',
            'language' => 'fa',
        ]);

        Course::create([
            'course_name' => 'دوره مقدماتی PHP',
            'course_code' => 'PHP-001',
            'description' => 'این دوره برای آموزش ابتدایی PHP می‌باشد.',
            'about' => 'در این دوره مباحث پایه‌ای و ساخت برنامه‌های CRUD آموزش داده می‌شود.',
            'instructor_id' => 2,
            'category_id' => 3,
            'tuition_fee' => 4000000,
            'capacity' => 60,
            'visibility' => true,
            'status' => 'active',
            'skill_level' => 'beginner',
            'language' => 'fa',
        ]);

        Course::create([
            'course_name' => 'دوره پیشرفته PHP',
            'course_code' => 'PHP-002',
            'description' => 'این دوره برای آموزش پیشرفته PHP می‌باشد.',
            'about' => 'در این دوره مباحث پیشرفته و ساخت برنامه‌های پیچیده آموزش داده می‌شود.',
            'instructor_id' => 2,
            'category_id' => 3,
            'tuition_fee' => 7000000,
            'capacity' => 45,
            'visibility' => true,
            'status' => 'active',
            'skill_level' => 'advanced',
            'language' => 'fa',
        ]);
    }
}
