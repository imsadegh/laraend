<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Role;

/**
//  * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    /**
     * The current password being used by the factory.
     */
    // protected static ?string $password;

    public function definition(): array
    {
        // Set the Faker locale to Persian
        $this->faker = \Faker\Factory::create('fa_IR'); // For Persian/Farsi

        return [
            // 'name' => fake()->name(),
            // 'email' => fake()->unique()->safeEmail(),
            // 'email_verified_at' => now(),
            // 'password' => static::$password ??= Hash::make('password'),
            // 'remember_token' => Str::random(10),

            'role_id' => $this->faker->numberBetween(1, 3),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'full_name' => function (array $attributes) {
                return $attributes['first_name'] . ' ' . $attributes['last_name'];
            },
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => '09' . $this->faker->numerify('#########'),
            'melli_code' => $this->faker->unique()->numerify('##########'),
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            // 'position_title' => $this->faker->jobTitle(),
            'position_title' => \Faker\Factory::create('en_US')->jobTitle(),
            'sex' => $this->faker->randomElement(['male', 'female', 'other']),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'zip_code' => $this->faker->postcode(),
            'avatar' => '/src/assets/images/avatars/avatar-' . $this->faker->numberBetween(1, 15) . '.png',
            'is_verified' => $this->faker->boolean(),
            'suspended' => $this->faker->boolean(),
            'email_verified_at' => $this->faker->boolean() ? $this->faker->dateTimeThisYear() : null,
            'password' => bcrypt('Sadegh@123'),  // You can adjust the default password here
            'remember_token' => \Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    // public function admin()
    // {
    //     return $this->state([
    //         'role_id' => Role::factory()->state(['name' => 'admin']),  // Assuming 'admin' is a role in the roles table
    //     ]);
    // }

    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function suspended()
    {
        return $this->state([
            'suspended' => true,
        ]);
    }

}
