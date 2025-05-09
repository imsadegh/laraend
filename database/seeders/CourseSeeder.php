<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Course::factory(6)->create(); // Creates 10 courses
        Course::factory()->create([
            'course_name' => 'دوره مقدماتی ویو',
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
            'visibility' => 'true',
            'status' => 'active',
        ]);
        Course::factory()->create([
            'course_name' => 'دوره پیشرفته ویو',
            'description' => 'این دوره برای آموزش پیشرفته Vue.js می‌باشد.',
            'about' => 'در این دوره مباحث پیشرفته و ساخت کامپوننت‌های پیچیده آموزش داده می‌شود.',
            'instructor_id' => 4,
            'visibility' => 'true',
            'status' => 'active',
        ]);
        Course::factory()->create([
            'course_name' => 'دوره مقدماتی لاراول',
            'description' => 'این دوره برای آموزش ابتدایی Laravel می‌باشد.',
            'about' => 'در این دوره مباحث پایه‌ای و ساخت برنامه‌های CRUD آموزش داده می‌شود.',
            'instructor_id' => 2,
            'visibility' => 'true',
            'status' => 'active',
        ]);
        Course::factory()->create([
            'course_name' => 'دوره پیشرفته لاراول',
            'description' => 'این دوره برای آموزش پیشرفته Laravel می‌باشد.',
            'about' => 'در این دوره مباحث پیشرفته و ساخت برنامه‌های پیچیده آموزش داده می‌شود.',
        ]);
        Course::factory()->create([
            'course_name' => 'دوره مقدماتی PHP',
            'description' => 'این دوره برای آموزش ابتدایی PHP می‌باشد.',
            'about' => 'در این دوره مباحث پایه‌ای و ساخت برنامه‌های CRUD آموزش داده می‌شود.',
        ]);
        Course::factory()->create([
            'course_name' => 'دوره پیشرفته PHP',
            'description' => 'این دوره برای آموزش پیشرفته PHP می‌باشد.',
            'about' => 'در این دوره مباحث پیشرفته و ساخت برنامه‌های پیچیده آموزش داده می‌شود.',
        ]);
    }
}
