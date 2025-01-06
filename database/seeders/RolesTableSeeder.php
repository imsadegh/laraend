<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('roles')->insert([
            ['name' => 'Student', 'description' => 'A student user'],
            ['name' => 'Teacher', 'description' => 'A teacher user'],
            ['name' => 'Assistant', 'description' => 'A teaching assistant'],
            ['name' => 'admin', 'description' => 'An admin user'],
        ]);
    }
}
