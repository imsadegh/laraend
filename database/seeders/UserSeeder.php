<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin User
        User::create([
            'first_name' => 'صادق',
            'last_name' => 'حیدرپور',
            'full_name' => 'صادق حیدرپور',
            'username' => 'sadegh904',
            'email' => 'admin@demo.com',
            'phone_number' => '09354555328',
            'email_verified_at' => now(),
            'password' => bcrypt('Sadegh@123'),
            'is_verified' => true,
            'suspended' => false,
            'role_id' => 5,  // Assigning the 'admin' role (role_id = 5)
        ]);

        // Instructor User
        User::create([
            'first_name' => 'رضا',
            'last_name' => 'یزدانی',
            'full_name' => 'رضا یزدانی',
            'username' => 'instructor_demo',
            'email' => 'instructor@demo.com',
            'phone_number' => '09109643694',
            'email_verified_at' => now(),
            'password' => bcrypt('Sadegh@123'),
            'is_verified' => true,
            'suspended' => false,
            'role_id' => 2,  // Assigning the 'instructor' role (role_id = 2)
        ]);

        // Client/Student User
        User::create([
            'first_name' => 'علی',
            'last_name' => 'رضایی',
            'full_name' => 'علی رضایی',
            'username' => 'student_demo',
            'email' => 'client@demo.com',
            'phone_number' => '09165464654',
            'email_verified_at' => now(),
            'password' => bcrypt('Sadegh@123'),
            'is_verified' => true,
            'suspended' => false,
            'role_id' => 1,  // Assigning the 'client' role (role_id = 1)
        ]);

        // Additional test users (only if Faker is available - for development)
        if (class_exists('\Faker\Factory')) {
            User::factory(5)->create();
        }
    }
}
