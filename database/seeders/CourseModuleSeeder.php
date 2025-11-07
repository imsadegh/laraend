<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseModuleSeeder extends Seeder
{
    public function run(): void
    {
        // ensure IDs stay consistent
        DB::table('course_modules')->truncate();

        DB::table('course_modules')->insert([
            [
                'id'                         => 1,
                'course_id'                  => 1,
                'created_by'                 => 1,
                'title'                      => 'یه اموزش متنی',
                'type'                       => 'article',
                'content_url'                => null,
                'description'                => 'توضیحات این اموزش متنی',
                'article_content'            => 'محتوا متن......',
                'module_data'                => null,
                'position'                   => null,
                'visible'                    => true,
                'release_date'               => '2025-04-11 17:11:00',
                'is_mandatory'               => true,
                'estimated_duration_minutes' => 4,
                'view_count'                 => 0,
                'prerequisite_modules'       => null,
                'rating'                     => 0.00,
                'created_at'                 => '2025-04-17 13:41:29',
                'updated_at'                 => '2025-04-17 13:41:29',
                'deleted_at'                 => null,
            ],
            [
                'id'                         => 2,
                'course_id'                  => 1,
                'created_by'                 => 1,
                'title'                      => 'ویدیو اول',
                'type'                       => 'video',
                'content_url'                => null,
                'description'                => 'توضیحات فیلم',
                'article_content'            => null,
                'module_data'                => null,
                'position'                   => null,
                'visible'                    => true,
                'release_date'               => null,
                'is_mandatory'               => false,
                'estimated_duration_minutes' => 7,
                'view_count'                 => 0,
                'prerequisite_modules'       => null,
                'rating'                     => 0.00,
                'created_at'                 => '2025-04-17 13:42:09',
                'updated_at'                 => '2025-04-17 13:42:09',
                'deleted_at'                 => null,
            ],
        ]);
    }
}
