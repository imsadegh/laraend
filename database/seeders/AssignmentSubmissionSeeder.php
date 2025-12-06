<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        // empty out the table so our ids stay consistent
        DB::table('assignment_submissions')->truncate();

        $rows = [
            [
                'id'                => 1,
                'assignment_id'     => 1,
                'user_id'           => 3,
                'submission_date'   => '2025-04-15 10:21:14',
                'file_path'         => null,
                'comments'          => 'تمرین اولو نوشتم',
                'score'             => 19.00,
                'revision_number'   => 3,
                'is_late'           => false,
                'feedback'          => 'آفرین خوبه',
                'last_reviewed_at'  => '2025-04-17 12:22:18',
                'reviewed_by'       => 2,
                'grade_visibility'  => true,
                'metadata'          => null,
                'created_at'        => '2025-04-14 12:22:05',
                'updated_at'        => '2025-04-17 12:22:18',
                'deleted_at'        => null,
            ],
            [
                'id'                => 2,
                'assignment_id'     => 3,
                'user_id'           => 3,
                'submission_date'   => '2025-04-15 10:46:31',
                'file_path'         => null,
                'comments'          => 'تمرینو نوشتم دوباره',
                'score'             => 22.00,
                'revision_number'   => 4,
                'is_late'           => false,
                'feedback'          => '',        // no feedback yet
                'last_reviewed_at'  => '2025-04-15 10:47:09',
                'reviewed_by'       => 2,
                'grade_visibility'  => true,
                'metadata'          => null,
                'created_at'        => '2025-04-15 08:07:55',
                'updated_at'        => '2025-04-15 10:47:09',
                'deleted_at'        => null,
            ],
        ];

        DB::table('assignment_submissions')->insert($rows);

        // Reset PostgreSQL sequence after inserting seed data with explicit IDs
        DB::statement("SELECT setval('assignment_submissions_id_seq', (SELECT COALESCE(MAX(id), 0) FROM assignment_submissions) + 1)");
    }
}
