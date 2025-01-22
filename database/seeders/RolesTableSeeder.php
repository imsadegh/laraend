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
        $roles =[
            ['id' => 1, 'name' => 'student', 'description' => 'A student user'],
            ['id' => 2, 'name' => 'instructor', 'description' => 'A instructor user'],
            ['id' => 3, 'name' => 'assistant', 'description' => 'A teaching assistant'],
            ['id' => 4, 'name' => 'manager', 'description' => 'A manager for class administrative purposes'],
            ['id' => 5, 'name' => 'admin', 'description' => 'A manager for website administrative purposes'],
        ];

        // Insert data into the 'categories' table
        DB::table('roles')->insert($roles);
    }
}
