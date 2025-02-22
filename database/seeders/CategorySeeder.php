<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear the table
        DB::table('categories')->truncate();

        // Example category data
        $categories = [
            ['name' => 'فناوری', 'parent_category_id' => null],
            ['name' => 'برنامه نویسی', 'parent_category_id' => 1], // Subcategory of 'Technology'
            ['name' => 'توسعه وب', 'parent_category_id' => 2], // Subcategory of 'Programming'
            ['name' => 'سلامت و ورزش', 'parent_category_id' => null],
            ['name' => 'رژیم', 'parent_category_id' => 4], // Subcategory of 'Health & Fitness'
            ['name' => 'سبک زندگی', 'parent_category_id' => null],
            ['name' => 'عکاسی', 'parent_category_id' => 6], // Subcategory of 'Lifestyle'
        ];

        // Insert data into the 'categories' table
        DB::table('categories')->insert($categories);
    }
}
