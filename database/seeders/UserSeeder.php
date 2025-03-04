<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::factory()->create([
            'first_name' => 'صادق',
            'last_name' => 'حیدرپور',
            'full_name' => 'صادق حیدرپور',
            'username' => 'sadegh904',
            'email' => 'admin@demo.com',
            'phone_number' => '09364565465',
            'email_verified_at' => now(),
            'password' => bcrypt('Sadegh@123'),
            'is_verified' => true,
            'suspended' => false,
            'role_id' => 5,  // Assigning the 'admin' role (role_id = 5)
            'avatar' => '',
        ]);

        User::factory()->create([
            // 'first_name' => 'رضا',
            // 'last_name' => 'یزدانی',
            // 'full_name' => 'رضا یزدانی',
            // 'username' => 'ya_reza',
            'email' => 'instructor@demo.com',
            'phone_number' => '09109643694',
            'email_verified_at' => now(),
            'password' => bcrypt('Sadegh@123'),
            'is_verified' => true,
            'suspended' => false,
            'role_id' => 2,  // Assigning the 'instructor' role (role_id = 2)
            'avatar' => '',
        ]);

        User::factory()->create([
            // 'first_name' => 'علی',
            // 'last_name' => 'رضایی',
            // 'full_name' => 'علی رضایی',
            // 'username' => 'ali_rezaei',
            'email' => 'client@demo.com',
            'phone_number' => '09165464654',
            'email_verified_at' => now(),
            'password' => bcrypt('Sadegh@123'),
            'is_verified' => true,
            'suspended' => false,
            'role_id' => 1,  // Assigning the 'client' role (role_id = 1)
            'avatar' => '',
        ]);

        // Create 10 users
        User::factory(4)->create();
    }
}
