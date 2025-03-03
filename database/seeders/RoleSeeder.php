<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // $roles =[
        //     ['id' => 1, 'name' => 'student', 'description' => 'A student user'],
        //     ['id' => 2, 'name' => 'instructor', 'description' => 'A instructor user'],
        //     ['id' => 3, 'name' => 'assistant', 'description' => 'A teaching assistant'],
        //     ['id' => 4, 'name' => 'manager', 'description' => 'A manager for class administrative purposes'],
        //     ['id' => 5, 'name' => 'admin', 'description' => 'A manager for website administrative purposes'],
        // ];
        // Insert data into the 'categories' table
        // DB::table('roles')->insert($roles);


        // Clear the roles table before inserting new roles
        Role::truncate();

        // Now insert the roles
        Role::create(['id' => 1, 'name' => 'student', 'description' => 'A student user']);
        Role::create(['id' => 2, 'name' => 'instructor', 'description' => 'A instructor user']);
        Role::create(['id' => 3, 'name' => 'assistant', 'description' => 'A teaching assistant']);
        Role::create(['id' => 4, 'name' => 'manager', 'description' => 'A manager for class administrative purposes']);
        Role::create(['id' => 5, 'name' => 'admin', 'description' => 'A manager for website administrative purposes']);

    }
}
