<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class FixedUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ensure roles exist or create them
        // You can adjust these if you already have role data in your database.
        // Creating roles explicitly (if needed):
        // Role::firstOrCreate(['id' => 1, 'name' => 'client']);
        // Role::firstOrCreate(['id' => 2, 'name' => 'instructor']);
        // Role::firstOrCreate(['id' => 5, 'name' => 'admin']);

        // Create the fixed users using firstOrCreate to avoid duplication
        User::firstOrCreate([
            'email' => 'instructor@demo.com',
        ], [
            'first_name' => 'رضا',
            'last_name' => 'یزدانی',
            'full_name' => 'رضا یزدانی',
            'username' => 'instructor@demo.com',
            'phone_number' => '09109643694',
            'email_verified_at' => now(), // Verified
            'password' => bcrypt('Sadegh@123'), // Password
            'is_verified' => true, // Verified
            'suspended' => false,
            'role_id' => 2,  // Assigning the 'instructor' role (role_id = 2)
        ]);

        User::firstOrCreate([
            'email' => 'admin@demo.com',
        ], [
            'first_name' => 'صادق',
            'last_name' => 'حیدرپور',
            'full_name' => 'صادق حیدرپور',
            'username' => 'admin@demo.com',
            'phone_number' => '09364565465',
            'email_verified_at' => now(), // Verified
            'password' => bcrypt('Sadegh@123'), // Password
            'is_verified' => true, // Verified
            'suspended' => false,
            'role_id' => 5,  // Assigning the 'admin' role (role_id = 5)
        ]);

        User::firstOrCreate([
            'email' => 'client@demo.com',
        ], [
            'first_name' => 'علی',
            'last_name' => 'رضایی',
            'full_name' => 'علی رضایی',
            'username' => 'client@demo.com',
            'phone_number' => '09165464654',
            'email_verified_at' => now(), // Verified
            'password' => bcrypt('Sadegh@123'), // Password
            'is_verified' => true, // Verified
            'suspended' => false,
            'role_id' => 1,  // Assigning the 'client' role (role_id = 1)
        ]);
    }
}
