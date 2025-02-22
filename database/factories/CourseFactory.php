<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Set the Faker locale to Persian
        $this->faker = \Faker\Factory::create('fa_IR'); // For Persian/Farsi


        return [
            // 'course_name' => $this->faker->word() . ' Course',
            'course_name' => $this->faker->unique()->sentence(),
            'course_code' => $this->faker->unique()->regexify('[A-Z]{4}[0-9]{4}'),
            'instructor_id' => User::factory(),  // Assuming the User factory is available for instructor
            'assistant_id' => User::factory(),  // Optional assistant
            'category_id' => $this->faker->numberBetween(1, 7),
            'capacity' => $this->faker->numberBetween(20, 100),  // Random capacity between 20 and 100
            'visibility' => $this->faker->boolean(),  // Random visibility (true/false)
            'featured' => $this->faker->boolean(),  // Random featured (true/false)
            'description' => $this->faker->paragraph(),  // Random course description
            'about' => $this->faker->paragraph(),  // Random about section for course
            'discussion_group_url' => $this->faker->url(),  // Random URL for discussion group
            'status' => $this->faker->randomElement(['active', 'archived', 'draft']),  // Random status
            'is_finished' => $this->faker->boolean(),  // Random finished status
            'enrolled_students_count' => $this->faker->numberBetween(0, 100),  // Random student enrollment count
            'allow_waitlist' => $this->faker->boolean(),  // Random boolean for waitlist
            'start_date' => $this->faker->dateTimeBetween('now', '+1 month'),  // Random start date
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+6 months'),  // Random end date
            'prerequisites' => json_encode($this->faker->words(3)),  // Random prerequisites in JSON format
            'tags' => json_encode($this->faker->words(5)),  // Random tags in JSON format
            'thumbnail_url' => $this->faker->imageUrl(),  // Random image URL for thumbnail
            'rating' => $this->faker->randomFloat(2, 0, 5),  // Random course rating between 0 and 5
        ];
    }
}
