<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
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
            ['name' => 'Technology', 'parent_category_id' => null],
            ['name' => 'Programming', 'parent_category_id' => 1], // Subcategory of 'Technology'
            ['name' => 'Web Development', 'parent_category_id' => 2], // Subcategory of 'Programming'
            ['name' => 'Health & Fitness', 'parent_category_id' => null],
            ['name' => 'Nutrition', 'parent_category_id' => 4], // Subcategory of 'Health & Fitness'
            ['name' => 'Lifestyle', 'parent_category_id' => null],
            ['name' => 'Photography', 'parent_category_id' => 6], // Subcategory of 'Lifestyle'
        ];

        // Insert data into the 'categories' table
        DB::table('categories')->insert($categories);
    }
}
