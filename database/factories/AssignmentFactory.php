<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    protected $model = \App\Models\Assignment::class;

    public function definition()
    {
        // Set the Faker locale to Persian
        $this->faker = \Faker\Factory::create('fa_IR');

        return [
            // 'course_id' => Course::factory(),
            'course_id' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'submission_deadline' => $this->faker->dateTimeBetween('now', '+1 month'),

            'requirements' => json_encode([
                'rubric' => $this->faker->text(),
                'special_instructions' => $this->faker->text(),
            ]),

            'max_score' => $this->faker->randomFloat(2, 50, 100), // Decimal (5,2)
            'is_active' => $this->faker->boolean(),
            'type' => $this->faker->randomElement(['individual', 'group']),
            'allow_late_submission' => $this->faker->boolean(),
            'visible' => $this->faker->boolean(),
            'late_submission_penalty' => $this->faker->optional()->numberBetween(5, 20), // Nullable
            'resources' => json_encode([
                'links' => [$this->faker->url()],
                'files' => [$this->faker->filePath()],
            ]),
            'revision_limit' => $this->faker->numberBetween(1, 3),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'last_submission_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
