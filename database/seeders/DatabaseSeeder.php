<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // \Artisan::call('migrate:fresh');

        // $this->call(RoleSeeder::class);
        // $this->call(UserSeeder::class);
        // $this->call(CategorySeeder::class);
        // $this->call(CourseSeeder::class);
        // $this->call(AssignmentSeeder::class);
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            CourseSeeder::class,
            CourseModuleSeeder::class,
            AssignmentSeeder::class,
            AssignmentSubmissionSeeder::class,
            TuitionHistorySeeder::class,
            CourseEnrollmentSeeder::class,

        ]);


        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
