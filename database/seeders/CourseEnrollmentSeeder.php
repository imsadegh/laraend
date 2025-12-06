<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        // reset table so IDs stay consistent
        DB::table('course_enrollments')->truncate();

        DB::table('course_enrollments')->insert([
            [
                'id'                     => 1,
                'course_id'              => 1,
                'user_id'                => 3,
                'status'                 => 'enrolled',
                'active'                 => true,
                'eligible_for_enrollment'=> true,
                'enrollment_date'        => null,
                'completion_date'        => null,
                'last_accessed_at'       => null,
                'progress_percentage'    => 0,
                'final_score'            => null,
                'certificate_data'       => null,
                'created_at'             => '2025-04-17 12:52:03',
                'updated_at'             => '2025-04-17 12:52:31',
            ],
        ]);

        // Reset PostgreSQL sequence after inserting seed data with explicit IDs
        DB::statement("SELECT setval('course_enrollments_id_seq', (SELECT COALESCE(MAX(id), 0) FROM course_enrollments) + 1)");
    }
}
