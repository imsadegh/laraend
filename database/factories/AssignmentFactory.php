<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Assignment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Set the Faker locale to Persian
        $this->faker->locale('fa_IR');  // Persian (Farsi)


        return [
            'course_id' => Course::factory(),  // Assuming the Course factory is available
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'submission_deadline' => $this->faker->dateTimeBetween('now', '+1 month'),
            'requirements' => json_encode([
                'rubric' => $this->faker->text(),
                'special_instructions' => $this->faker->text(),
            ]),
            'max_score' => $this->faker->randomFloat(2, 50, 100),
            'is_active' => $this->faker->boolean(),
            'type' => $this->faker->randomElement(['individual', 'group']),
            'allow_late_submission' => $this->faker->boolean(),
            'visible' => $this->faker->boolean(),
            'late_submission_penalty' => $this->faker->numberBetween(5, 20),
            'resources' => json_encode([
                'links' => $this->faker->url(),
                'files' => $this->faker->filePath(),
            ]),
            'revision_limit' => $this->faker->numberBetween(1, 3),
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'last_submission_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
