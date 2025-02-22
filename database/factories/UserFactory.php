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
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\User::class;

    /**
     * The current password being used by the factory.
     */
    // protected static ?string $password;

    /**
     * Define the model's default state.
     *
    //  * @return array<string, mixed>
     * @return array
     */
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

            'role_id' => $this->faker->numberBetween(1, 5),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'full_name' => function (array $attributes) {
                return $attributes['first_name'] . ' ' . $attributes['last_name'];
            },
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => '09' . $this->faker->numerify('#########'),
            'sex' => $this->faker->randomElement(['male', 'female', 'other']),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'zip_code' => $this->faker->postcode(),
            'avatar' => $this->faker->imageUrl(),  // You can customize this or use default placeholders
            'is_verified' => $this->faker->boolean(),
            'suspended' => $this->faker->boolean(),
            'email_verified_at' => $this->faker->boolean() ? $this->faker->dateTimeThisYear() : null,
            'password' => bcrypt('password'),  // You can adjust the default password here
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
    public function admin()
    {
        return $this->state([
            'role_id' => Role::factory()->state(['name' => 'admin']),  // Assuming 'admin' is a role in the roles table
        ]);
    }

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
