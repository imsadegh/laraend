<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Clear the table
        DB::table('categories')->truncate();

        // todo: Also check the LmsMyCourses.vue file for changing the tags
        $categories = [
            ['name' => 'مبانی متافیزیک اسلامی', 'parent_category_id' => null, 'description' => 'شامل شناخت هستی، رابطه روح و بدن، مراتب وجود، و مفاهیم انرژی در نگاه دینی.'],
            ['name' => 'دعا درمانی و شفای روح', 'parent_category_id' => 1, 'description' => 'آموزش کاربردی دعاها، آداب دعا، اثرات روحی و جسمی ادعیه، و شفای امراض از منظر دینی.'],
            ['name' => 'اذکار، اسماء الهی و کاربرد آن‌ها', 'parent_category_id' => 2, 'description' => 'شناخت اسماء حسنی، ذکر درمانی، تنظیم برنامه ذکر روزانه، و تأثیر آن‌ها بر روح و جسم.'],
            ['name' => 'تعادل انرژی و پاک‌سازی روحی', 'parent_category_id' => null, 'description' => 'موضوعاتی مانند چاکرا از نگاه اسلامی، وضو، غسل، نماز و اثرات پاک‌کنندگی اعمال عبادی.'],
            ['name' => 'حفاظت روحی و باطنی', 'parent_category_id' => 4, 'description' => 'روش‌های مقابله با انرژی‌های منفی، چشم زخم، سحر و جن، با استفاده از آیات و ادعیه مأثور.'],
            ['name' => 'ارتباط با عوالم معنوی', 'parent_category_id' => null, 'description' => 'تفسیر خواب، رؤیاهای صادقه، حالات مکاشفه، و آداب سیر و سلوک باطنی.'],
            ['name' => 'آداب زندگی متافیزیکی در سبک اسلامی', 'parent_category_id' => 6, 'description' => 'تنظیم سبک زندگی مبتنی بر معرفت باطنی: تغذیه، خواب، سخن گفتن، حضور قلب و سبک پوشش.'],
        ];

        // Insert data into the 'categories' table
        DB::table('categories')->insert($categories);
    }
}
